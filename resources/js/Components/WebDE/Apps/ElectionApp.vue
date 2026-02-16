<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import * as pdfjsLib from 'pdfjs-dist';

// Set worker source
pdfjsLib.GlobalWorkerOptions.workerSrc = `//cdnjs.cloudflare.com/ajax/libs/pdf.js/${pdfjsLib.version}/pdf.worker.min.js`;

const props = defineProps({
    isActive: Boolean
});

const page = usePage();
const elections = ref([]);
const selectedElection = ref(null);
const userStatus = ref(null);
const loading = ref(true);
const votingProcessing = ref(false);
const showManifesto = ref(false);
const currentManifestoUrl = ref(null);
const votes = ref({}); // { position: candidate_id }

const fetchElections = async () => {
    try {
        const response = await axios.get('/api/v1/elections');
        elections.value = response.data.elections;
    } catch (error) {
        console.error('Failed to fetch elections:', error);
    } finally {
        loading.value = false;
    }
};

const viewElection = async (id) => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/v1/elections/${id}`);
        selectedElection.value = response.data;
        userStatus.value = response.data.user_status;
    } catch (error) {
        console.error('Failed to fetch election details:', error);
    } finally {
        loading.value = false;
    }
};

const selectCandidate = (position, candidateId) => {
    votes.value[position] = candidateId;
};

const openManifesto = (url) => {
    if (!url) return;
    currentManifestoUrl.value = url;
    showManifesto.value = true;
};

const castVote = async () => {
    if (!selectedElection.value) return;
    if (Object.keys(votes.value).length === 0) {
        alert('Please select at least one candidate.');
        return;
    }
    
    if (!confirm('Are you sure? Once cast, your vote cannot be changed.')) return;

    votingProcessing.value = true;
    try {
        await axios.post(`/api/v1/elections/${selectedElection.value.election.id}/vote`, {
            votes: votes.value
        });
        alert('Vote cast successfully!');
        // Refresh status
        await viewElection(selectedElection.value.election.id);
        votes.value = {};
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to cast vote.');
    } finally {
        votingProcessing.value = false;
    }
};

const exitElection = () => {
    selectedElection.value = null;
    votes.value = {};
    fetchElections();
};

onMounted(() => {
    fetchElections();
});
</script>

<template>
    <div class="election-app">
        <!-- HEADER -->
        <header class="app-header">
            <div class="header-left">
                <button v-if="selectedElection" @click="exitElection" class="back-btn">← Back</button>
                <h2 v-else>Digital Voting Booth</h2>
            </div>
            <div class="header-right" v-if="selectedElection">
                 <span class="status-badge" :class="selectedElection.election.status">
                    {{ selectedElection.election.status }}
                 </span>
                 <span class="timer">Ends: {{ new Date(selectedElection.election.ends_at).toLocaleDateString() }}</span>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="app-content" v-if="!loading">
            
            <!-- LIST VIEW -->
            <div v-if="!selectedElection" class="election-list">
                <div v-if="elections.length === 0" class="empty-state">
                    <p>No active elections at this time.</p>
                </div>
                
                <div 
                    v-for="election in elections" 
                    :key="election.id"
                    class="election-card"
                    @click="viewElection(election.id)"
                >
                    <div class="election-status-indicator" :class="election.status"></div>
                    <div class="election-info">
                        <h3>{{ election.title }}</h3>
                        <p>
                            Starting: {{ new Date(election.start_date).toLocaleDateString() }} • 
                            <span v-if="election.has_voted" class="text-green-400">Voted ✅</span>
                            <span v-else-if="election.can_vote" class="text-blue-400">Vote Now 🗳️</span>
                            <span v-else class="text-gray-400">Viewing Only 👀</span>
                        </p>
                    </div>
                    <div class="election-arrow">→</div>
                </div>
            </div>

            <!-- DETAIL VIEW / BALLOT -->
            <div v-else class="election-detail">
                <div class="election-intro">
                    <h1>{{ selectedElection.election.title }}</h1>
                    <p>{{ selectedElection.election.description }}</p>
                </div>

                <div v-if="selectedElection.user_status.has_voted" class="already-voted-banner">
                    ✅ You have already cast your vote in this election. Thank you for participating!
                </div>

                <!-- CANDIDATE POSITIONS -->
                <div class="positions-container">
                    <div v-for="(candidates, position) in selectedElection.candidates" :key="position" class="position-group">
                        <h2 class="position-Title">{{ position }}</h2>
                        
                        <div class="candidates-grid">
                            <div 
                                v-for="candidate in candidates" 
                                :key="candidate.id"
                                class="candidate-card"
                                :class="{ 
                                    'selected': votes[position] === candidate.id,
                                    'voted': candidate.is_voted
                                }"
                                @click="!selectedElection.user_status.has_voted && selectCandidate(position, candidate.id)"
                            >
                                <div class="candidate-photo">
                                    <img :src="candidate.photo || 'https://ui-avatars.com/api/?name=' + candidate.name + '&background=random'" :alt="candidate.name">
                                </div>
                                <div class="candidate-info">
                                    <h3>{{ candidate.name }}</h3>
                                    <p class="candidate-bio">{{ candidate.bio || 'No vision statement provided.' }}</p>
                                    
                                    <button 
                                        v-if="candidate.manifesto_url" 
                                        @click.stop="openManifesto(candidate.manifesto_url)"
                                        class="manifesto-btn"
                                    >
                                        📄 View Manifesto
                                    </button>
                                </div>
                                
                                <div v-if="!selectedElection.user_status.has_voted" class="selection-indicator">
                                    {{ votes[position] === candidate.id ? 'Selected' : 'Select' }}
                                </div>
                                <div v-else-if="candidate.is_voted" class="voted-indicator">
                                    Your Choice ✅
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT ACTION -->
                <div v-if="!selectedElection.user_status.has_voted && Object.keys(votes).length > 0" class="vote-action-bar">
                    <div class="summary">
                        You have selected candidates for {{ Object.keys(votes).length }} positions.
                    </div>
                    <button @click="castVote" :disabled="votingProcessing" class="submit-vote-btn">
                        {{ votingProcessing ? 'Submitting...' : 'Confirm & Cast Vote' }}
                    </button>
                </div>
            </div>

        </div>

        <div v-else class="loading-state">
            <div class="spinner"></div> Loading Election Data...
        </div>

        <!-- MANIFESTO MODAL -->
        <div v-if="showManifesto" class="modal-overlay" @click="showManifesto = false">
            <div class="modal-content" @click.stop>
                <header class="modal-header">
                    <h3>Candidate Manifesto</h3>
                    <button @click="showManifesto = false">×</button>
                </header>
                <div class="pdf-container">
                    <iframe :src="currentManifestoUrl" frameborder="0" width="100%" height="100%"></iframe>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.election-app {
    height: 100%;
    display: flex;
    flex-direction: column;
    color: #fff;
    background: #0f1218;
    overflow: hidden;
    position: relative;
}

/* HEADER */
.app-header {
    flex-shrink: 0;
    height: 60px;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    background: rgba(20, 20, 28, 0.5);
}
.header-left h2 { font-size: 18px; font-weight: 600; }
.back-btn { background: none; border: none; color: #3b82f6; cursor: pointer; font-weight: 600; font-size: 14px; }
.back-btn:hover { text-decoration: underline; }
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    margin-right: 12px;
}
.status-badge.active { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.status-badge.upcoming { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.status-badge.completed { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
.timer { font-size: 12px; color: rgba(255,255,255,0.6); }

/* CONTENT SCROLL */
.app-content {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

/* LIST VIEW */
.election-card {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.03);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
}
.election-card:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.1); }
.election-status-indicator { width: 4px; height: 40px; border-radius: 2px; margin-right: 16px; }
.election-status-indicator.active { background: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
.election-info h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
.election-info p { font-size: 12px; color: rgba(255,255,255,0.6); }
.election-arrow { margin-left: auto; color: rgba(255,255,255,0.3); }

/* DETAIL VIEW */
.election-intro { margin-bottom: 32px; text-align: center; max-width: 600px; margin: 0 auto 32px auto; }
.election-intro h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
.election-intro p { font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.5; }

.already-voted-banner {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #10b981;
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    margin-bottom: 24px;
    font-size: 13px;
    font-weight: 500;
}

.position-group { margin-bottom: 40px; }
.position-Title {
    font-size: 18px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 8px;
    margin-bottom: 16px;
    color: #94a3b8;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.candidate-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    gap: 16px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}
.candidate-card:hover { background: rgba(255,255,255,0.06); }
.candidate-card.selected {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}
.candidate-card.voted {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    opacity: 0.8;
}

.candidate-photo img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.1);
}
.candidate-info { flex: 1; }
.candidate-info h3 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
.candidate-bio { font-size: 11px; color: rgba(255,255,255,0.5); line-height: 1.4; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.manifesto-btn {
    font-size: 10px;
    padding: 4px 8px;
    background: rgba(255,255,255,0.1);
    border: none;
    border-radius: 4px;
    color: #fff;
    cursor: pointer;
}
.manifesto-btn:hover { background: rgba(255,255,255,0.2); }

.selection-indicator {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    color: rgba(255,255,255,0.2);
}
.candidate-card.selected .selection-indicator { color: #3b82f6; }
.voted-indicator {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 10px;
    font-weight: 700;
    color: #10b981;
}

/* VOTE ACTION BAR */
.vote-action-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: #1e293b;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255,255,255,0.1);
    z-index: 50;
    /* In a real integration this would need to be inside the window container */
}
.summary { font-size: 13px; color: #94a3b8; }
.submit-vote-btn {
    background: #3b82f6;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}
.submit-vote-btn:hover { background: #2563eb; }
.submit-vote-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* MODAL */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: #1e293b;
    width: 90%;
    height: 90%;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.modal-header {
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #0f1218;
}
.modal-header button { background: none; border: none; font-size: 24px; color: #fff; cursor: pointer; }
.pdf-container { flex: 1; background: #333; }
</style>
