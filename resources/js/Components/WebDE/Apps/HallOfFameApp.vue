<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const members = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/hall-of-fame');
        members.value = response.data.members;
    } catch (error) {
        console.error('Failed to fetch Hall of Fame:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="hof-app">
        <header class="hof-header">
            <h2>🏆 Hall of Fame</h2>
            <p>Honoring the visionaries who built this community.</p>
        </header>

        <div class="hof-content" v-if="!loading">
            <div class="timeline">
                <div 
                    v-for="(member, index) in members" 
                    :key="member.id"
                    class="timeline-item"
                    :class="{ 'left': index % 2 === 0, 'right': index % 2 !== 0 }"
                >
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">{{ member.batch ? `Batch ${member.batch}` : 'Legend' }}</div>
                    
                    <div class="timeline-card">
                        <div class="member-header">
                            <img :src="member.image" :alt="member.name" class="member-avatar">
                            <div class="member-intro">
                                <h3>{{ member.name }}</h3>
                                <span class="role-badge">{{ member.role }}</span>
                            </div>
                        </div>
                        
                        <div class="member-body">
                            <p class="achievement"><strong>🏅 Achievement:</strong> {{ member.achievements }}</p>
                            <p class="contribution">{{ member.contribution }}</p>
                        </div>

                        <div class="member-footer" v-if="member.links && Object.keys(member.links).length">
                            <a 
                                v-for="(url, platform) in member.links" 
                                :key="platform" 
                                :href="url" 
                                target="_blank" 
                                class="social-link"
                            >
                                {{ platform }} ↗
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="members.length === 0" class="empty-state">
                No legends found... yet. You could be next!
            </div>
        </div>

        <div v-else class="loading-state">
            <div class="spinner"></div> Loading Legends...
        </div>
    </div>
</template>

<style scoped>
.hof-app {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #1a0f1f 0%, #0f1218 100%);
    color: #fff;
    overflow: hidden;
}

.hof-header {
    flex-shrink: 0;
    padding: 24px;
    text-align: center;
    background: rgba(0, 0, 0, 0.2);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.hof-header h2 { font-size: 24px; font-weight: 700; color: #fbbf24; margin-bottom: 4px; text-shadow: 0 2px 10px rgba(251, 191, 36, 0.3); }
.hof-header p { font-size: 13px; color: rgba(255, 255, 255, 0.6); }

.hof-content {
    flex: 1;
    overflow-y: auto;
    padding: 40px 20px;
}

/* TIMELINE */
.timeline {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}
.timeline::after {
    content: '';
    position: absolute;
    width: 2px;
    background: rgba(255, 255, 255, 0.1);
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -1px;
}

.timeline-item {
    padding: 10px 40px;
    position: relative;
    width: 50%;
    box-sizing: border-box;
    opacity: 0;
    animation: fadeIn 0.5s forwards;
}

.timeline-item.left { left: 0; text-align: right; }
.timeline-item.right { left: 50%; text-align: left; }

.timeline-dot {
    width: 16px;
    height: 16px;
    background: #fbbf24;
    border: 4px solid #1a0f1f;
    border-radius: 50%;
    position: absolute;
    top: 24px;
    right: -8px;
    z-index: 10;
    box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.3);
}
.timeline-item.right .timeline-dot { left: -8px; }

.timeline-date {
    position: absolute;
    top: 24px;
    right: -100px;
    width: 80px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.4);
}
.timeline-item.right .timeline-date { left: -100px; text-align: right; }

.timeline-card {
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(5px);
    transition: transform 0.2s;
}
.timeline-card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.08); }

.member-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.timeline-item.left .member-header { flex-direction: row-reverse; }

.member-avatar { width: 48px; height: 48px; border-radius: 50%; border: 2px solid #fbbf24; }

.member-intro h3 { font-size: 16px; font-weight: 700; color: #fff; margin: 0; }
.role-badge { font-size: 10px; background: rgba(251, 191, 36, 0.15); color: #fbbf24; padding: 2px 6px; border-radius: 4px; font-weight: 600; text-transform: uppercase; }

.member-body { font-size: 13px; line-height: 1.5; color: rgba(255, 255, 255, 0.7); margin-bottom: 12px; }
.member-body p { margin: 4px 0; }
.achievement { color: #e2e8f0; }

.member-footer { display: flex; gap: 8px; flex-wrap: wrap; }
.timeline-item.left .member-footer { justify-content: flex-end; }
.social-link { font-size: 11px; color: #3b82f6; text-decoration: none; background: rgba(59, 130, 246, 0.1); padding: 2px 8px; border-radius: 4px; transition: all 0.2s; }
.social-link:hover { background: rgba(59, 130, 246, 0.2); }

@keyframes fadeIn {
    to { opacity: 1; }
}
</style>
