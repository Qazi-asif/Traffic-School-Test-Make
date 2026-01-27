<template>
  <div class="quiz-component">
    <div v-if="!quizStarted" class="text-center">
      <h3>{{ isFinalExam ? 'Final Exam' : `Chapter ${chapterTitle} Quiz` }}</h3>
      <p>{{ questions.length }} questions</p>
      <button @click="startQuiz" class="btn btn-primary">Start Quiz</button>
    </div>
    
    <div v-else-if="!quizCompleted" class="quiz-active">
      <div class="progress mb-3">
        <div class="progress-bar" :style="{width: progressPercentage + '%'}"></div>
      </div>
      
      <div class="question-counter mb-3">
        Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
      </div>
      
      <div class="question-card card">
        <div class="card-body">
          <h5>{{ currentQuestion.question_text }}</h5>
          
          <div v-if="currentQuestion.question_type === 'multiple_choice'" class="options">
            <div v-for="(option, index) in currentQuestion.options" :key="index" class="form-check">
              <input 
                :id="`option-${currentQuestion.id}-${index}`"
                :checked="answers[currentQuestion.id] === option"
                @change="answers[currentQuestion.id] = option"
                :value="option"
                type="radio" 
                class="form-check-input"
                :name="`question-${currentQuestion.id}`"
              >
              <label :for="`option-${currentQuestion.id}-${index}`" class="form-check-label">{{ option }}</label>
            </div>
          </div>
          
          <div v-else class="options">
            <div class="form-check">
              <input 
                :id="`true-option-${currentQuestion.id}`"
                :checked="answers[currentQuestion.id] === 'true'"
                @change="answers[currentQuestion.id] = 'true'"
                value="true"
                type="radio" 
                class="form-check-input"
                :name="`question-${currentQuestion.id}`"
              >
              <label :for="`true-option-${currentQuestion.id}`" class="form-check-label">True</label>
            </div>
            <div class="form-check">
              <input 
                :id="`false-option-${currentQuestion.id}`"
                :checked="answers[currentQuestion.id] === 'false'"
                @change="answers[currentQuestion.id] = 'false'"
                value="false"
                type="radio" 
                class="form-check-input"
                :name="`question-${currentQuestion.id}`"
              >
              <label :for="`false-option-${currentQuestion.id}`" class="form-check-label">False</label>
            </div>
          </div>
        </div>
      </div>
      
      <div class="navigation mt-3">
        <button 
          @click="previousQuestion" 
          :disabled="currentQuestionIndex === 0"
          class="btn btn-secondary me-2"
        >
          Previous
        </button>
        
        <button 
          v-if="currentQuestionIndex < questions.length - 1"
          @click="nextQuestion"
          class="btn btn-primary"
        >
          Next
        </button>
        
        <button 
          v-else
          @click="submitQuiz"
          class="btn btn-success"
        >
          Submit Quiz
        </button>
      </div>
    </div>
    
    <div v-else class="quiz-results text-center">
      <h3>Quiz Completed!</h3>
      <div class="score-display">
        <h2>{{ results.score }}%</h2>
        <p class="lead">{{ results.passed ? 'Passed' : 'Failed' }}</p>
      </div>
      <p>You answered {{ results.correct_answers }} out of {{ results.total_questions }} questions correctly.</p>
      <button @click="closeQuiz" class="btn btn-primary">Continue</button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['enrollmentId', 'chapterId', 'isFinalExam'],
  data() {
    return {
      questions: [],
      answers: {},
      currentQuestionIndex: 0,
      quizStarted: false,
      quizCompleted: false,
      results: null,
      chapterTitle: '',
      startTime: null
    }
  },
  computed: {
    currentQuestion() {
      return this.questions[this.currentQuestionIndex];
    },
    progressPercentage() {
      return ((this.currentQuestionIndex + 1) / this.questions.length) * 100;
    }
  },
  methods: {
    async fetchQuestions() {
      try {
        const url = this.isFinalExam 
          ? `/api/courses/${this.courseId}/final-exam-questions`
          : `/api/chapters/${this.chapterId}/questions`;
          
        const response = await fetch(url, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
        
        if (response.ok) {
          this.questions = await response.json();
        }
      } catch (error) {
        console.error('Error fetching questions:', error);
      }
    },
    
    async startQuiz() {
      this.quizStarted = true;
      this.startTime = new Date();
      
      try {
        const response = await fetch(`/api/enrollments/${this.enrollmentId}/start-quiz/${this.chapterId || ''}`, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin'
        });
      } catch (error) {
        console.error('Error starting quiz:', error);
      }
    },
    
    nextQuestion() {
      if (this.currentQuestionIndex < this.questions.length - 1) {
        this.currentQuestionIndex++;
      }
    },
    
    previousQuestion() {
      if (this.currentQuestionIndex > 0) {
        this.currentQuestionIndex--;
      }
    },
    
    async submitQuiz() {
      const timeSpent = Math.floor((new Date() - this.startTime) / 1000);
      
      const submissionData = {
        answers: Object.keys(this.answers).map(questionId => ({
          question_id: parseInt(questionId),
          selected_answer: this.answers[questionId]
        })),
        time_spent: timeSpent
      };
      
      try {
        const response = await fetch(`/api/enrollments/${this.enrollmentId}/submit-quiz/${this.chapterId || ''}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'same-origin',
          body: JSON.stringify(submissionData)
        });
        
        if (response.ok) {
          this.results = await response.json();
          this.quizCompleted = true;
        }
      } catch (error) {
        console.error('Error submitting quiz:', error);
      }
    },
    
    closeQuiz() {
      this.$emit('quiz-completed', this.results);
    }
  },
  
  mounted() {
    this.fetchQuestions();
  }
}
</script>
