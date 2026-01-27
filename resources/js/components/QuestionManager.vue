<template>
  <div class="question-manager">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Question Manager - {{ title }}</h2>
      <button class="btn btn-primary" @click="showCreateModal = true">
        <i class="fas fa-plus"></i> Add Question
      </button>
    </div>

    <div class="questions-list">
      <div v-for="question in questions" :key="question.id" class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <span class="badge bg-info me-2">{{ question.question_type }}</span>
            <span class="badge bg-secondary">{{ question.points }} pts</span>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-primary me-1" @click="editQuestion(question)">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" @click="deleteQuestion(question)">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <h6>{{ question.question_text }}</h6>
          <div v-if="question.question_type === 'multiple_choice'" class="options mt-2">
            <div v-for="(option, index) in question.options" :key="index" class="form-check">
              <input type="radio" :name="`question_${question.id}`" class="form-check-input" disabled>
              <label class="form-check-label" :class="{ 'text-success fw-bold': option === question.correct_answer }">
                {{ option }}
              </label>
            </div>
          </div>
          <div v-else class="options mt-2">
            <span class="badge bg-success">Correct Answer: {{ question.correct_answer }}</span>
          </div>
          <div v-if="question.explanation" class="explanation mt-2">
            <small class="text-muted"><strong>Explanation:</strong> {{ question.explanation }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Question Modal -->
    <div class="modal fade" :class="{ show: showCreateModal }" style="display: block" v-if="showCreateModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingQuestion ? 'Edit' : 'Create' }} Question</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveQuestion">
              <div class="mb-3">
                <label class="form-label">Question Text</label>
                <textarea v-model="questionForm.question_text" class="form-control" rows="3" required></textarea>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label">Question Type</label>
                  <select v-model="questionForm.question_type" class="form-select" required @change="onQuestionTypeChange">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True/False</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Points</label>
                  <input v-model="questionForm.points" type="number" class="form-control" min="1" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Order</label>
                  <input v-model="questionForm.order_index" type="number" class="form-control" min="1" required>
                </div>
              </div>

              <!-- Multiple Choice Options -->
              <div v-if="questionForm.question_type === 'multiple_choice'" class="mt-3">
                <label class="form-label">Answer Options</label>
                <div v-for="(option, index) in questionForm.options" :key="index" class="input-group mb-2">
                  <div class="input-group-text">
                    <input type="radio" :value="option" v-model="questionForm.correct_answer" class="form-check-input">
                  </div>
                  <input v-model="questionForm.options[index]" type="text" class="form-control" :placeholder="`Option ${index + 1}`" required>
                  <button type="button" class="btn btn-outline-danger" @click="removeOption(index)" v-if="questionForm.options.length > 2">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" @click="addOption" v-if="questionForm.options.length < 6">
                  <i class="fas fa-plus"></i> Add Option
                </button>
              </div>

              <!-- True/False Options -->
              <div v-if="questionForm.question_type === 'true_false'" class="mt-3">
                <label class="form-label">Correct Answer</label>
                <div class="form-check">
                  <input v-model="questionForm.correct_answer" type="radio" value="True" class="form-check-input" id="true">
                  <label class="form-check-label" for="true">True</label>
                </div>
                <div class="form-check">
                  <input v-model="questionForm.correct_answer" type="radio" value="False" class="form-check-input" id="false">
                  <label class="form-check-label" for="false">False</label>
                </div>
              </div>

              <div class="mt-3">
                <label class="form-label">Explanation (optional)</label>
                <textarea v-model="questionForm.explanation" class="form-control" rows="2"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveQuestion">Save Question</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuestionManager',
  props: ['chapterId', 'courseId', 'type'],
  data() {
    return {
      questions: [],
      showCreateModal: false,
      editingQuestion: null,
      questionForm: {
        question_text: '',
        question_type: 'multiple_choice',
        options: ['', '', '', ''],
        correct_answer: '',
        explanation: '',
        points: 1,
        order_index: 1
      }
    }
  },
  computed: {
    title() {
      return this.type === 'final_exam' ? 'Final Exam Questions' : 'Chapter Questions';
    }
  },
  methods: {
    loadQuestions() {
      // API call to load questions
    },
    editQuestion(question) {
      this.editingQuestion = question;
      this.questionForm = { ...question };
      this.showCreateModal = true;
    },
    saveQuestion() {
      // API call to save question
      this.closeModal();
    },
    deleteQuestion(question) {
      if (confirm('Are you sure?')) {
        // API call to delete question
      }
    },
    onQuestionTypeChange() {
      if (this.questionForm.question_type === 'true_false') {
        this.questionForm.options = ['True', 'False'];
        this.questionForm.correct_answer = '';
      } else {
        this.questionForm.options = ['', '', '', ''];
        this.questionForm.correct_answer = '';
      }
    },
    addOption() {
      this.questionForm.options.push('');
    },
    removeOption(index) {
      this.questionForm.options.splice(index, 1);
    },
    closeModal() {
      this.showCreateModal = false;
      this.editingQuestion = null;
      this.questionForm = {
        question_text: '',
        question_type: 'multiple_choice',
        options: ['', '', '', ''],
        correct_answer: '',
        explanation: '',
        points: 1,
        order_index: 1
      };
    }
  },
  mounted() {
    this.loadQuestions();
  }
}
</script>
