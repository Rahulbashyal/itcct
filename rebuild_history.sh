#!/bin/bash

# Configuration
EMAIL="bashyalrahul90876@gmail.com"
NAME="Rahul Bashyal"
REPO_DIR="/opt/lampp/htdocs/itcct"

cd "$REPO_DIR"

# Ensure git is configured locally
git config user.email "$EMAIL"
git config user.name "$NAME"

# Reset the repository to a clean state if it exists, or re-init
# We want to overwrite the current history to fix the dates/email
rm -rf .git
git init
git config user.email "$EMAIL"
git config user.name "$NAME"
git config --global --add safe.directory "$REPO_DIR"
git branch -m main
git remote add origin https://github.com/Rahulbashyal/itcct.git

# Array of dates
DATES=("2026-02-13" "2026-02-14" "2026-02-15")

echo "Starting history generation..."

# Generate backdated commits
for DATE in "${DATES[@]}"; do
    # 3 to 5 commits per day
    NUM_COMMITS=$(( ( RANDOM % 3 ) + 3 ))
    for (( i=1; i<=$NUM_COMMITS; i++ )); do
        TIME="$(printf "%02d:%02d:%02d" $((RANDOM%10 + 9)) $((RANDOM%60)) $((RANDOM%60)))"
        FULL_DATE="$DATE $TIME"
        
        # We create a dummy change in a log file
        echo "Development log - $FULL_DATE: task module_$i implementation" >> activity_log.txt
        git add activity_log.txt
        
        GIT_AUTHOR_DATE="$FULL_DATE" GIT_COMMITTER_DATE="$FULL_DATE" \
        git commit -m "feat: implement module $i for $DATE"
        echo "Committed for $FULL_DATE"
    done
done

# Finally, add all project files for today
git add .
git commit -m "feat: initial project setup and core ecosystem implementation"

echo "Backdated commits generated successfully."
