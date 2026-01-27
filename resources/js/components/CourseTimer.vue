<template>
    <div class="course-timer" v-if="timerRequired">
        <div class="timer-display" :class="{ 'timer-warning': timeRemaining < 60 }">
            <i class="fas fa-clock"></i>
            <span class="timer-text">{{ formattedTime }}</span>
        </div>
        <div class="timer-progress">
            <div class="progress-bar" :style="{ width: progressPercentage + '%' }"></div>
        </div>
        <div v-if="!timerCompleted" class="timer-message">
            Please complete the required time before proceeding.
        </div>
    </div>
</template>

<script>
export default {
    name: 'CourseTimer',
    props: {
        chapterId: {
            type: Number,
            required: true
        },
        requiredTime: {
            type: Number,
            required: true
        },
        sessionId: {
            type: Number,
            default: null
        }
    },
    data() {
        return {
            timeElapsed: 0,
            timerInterval: null,
            timerRequired: true,
            timerCompleted: false
        };
    },
    computed: {
        timeRemaining() {
            return Math.max(0, this.requiredTime - this.timeElapsed);
        },
        formattedTime() {
            const minutes = Math.floor(this.timeRemaining / 60);
            const seconds = this.timeRemaining % 60;
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        },
        progressPercentage() {
            return Math.min(100, (this.timeElapsed / this.requiredTime) * 100);
        }
    },
    mounted() {
        this.startTimer();
    },
    beforeUnmount() {
        this.stopTimer();
    },
    methods: {
        startTimer() {
            this.timerInterval = setInterval(() => {
                this.timeElapsed++;
                
                if (this.timeElapsed >= this.requiredTime) {
                    this.completeTimer();
                }
                
                // Update server every 10 seconds
                if (this.timeElapsed % 10 === 0) {
                    this.updateServer();
                }
            }, 1000);
        },
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.updateServer();
            }
        },
        completeTimer() {
            this.timerCompleted = true;
            this.stopTimer();
            this.$emit('timer-completed', {
                chapterId: this.chapterId,
                timeSpent: this.timeElapsed
            });
        },
        async updateServer() {
            if (!this.sessionId) return;
            
            try {
                await axios.post('/api/course-timer/update', {
                    session_id: this.sessionId,
                    time_spent: this.timeElapsed
                });
            } catch (error) {
                console.error('Failed to update timer:', error);
            }
        }
    }
};
</script>

<style scoped>
.course-timer {
    position: fixed;
    top: 80px;
    right: 20px;
    background: white;
    border: 2px solid #3490dc;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    min-width: 200px;
}

.timer-display {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 24px;
    font-weight: bold;
    color: #3490dc;
    margin-bottom: 10px;
}

.timer-warning {
    color: #e3342f;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.timer-progress {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3490dc, #6574cd);
    transition: width 0.3s ease;
}

.timer-message {
    font-size: 12px;
    color: #718096;
    text-align: center;
}
</style>
