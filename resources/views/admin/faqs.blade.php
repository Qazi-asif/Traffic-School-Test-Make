<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FAQs Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-question-circle me-2"></i>FAQs Management</h2>
                <p class="text-muted">Manage frequently asked questions by category</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                <i class="fas fa-plus me-2"></i>Add FAQ
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div id="faqs"></div>
            </div>
        </div>
    </div>

    <!-- Add FAQ Modal -->
    <div class="modal fade" id="addFaqModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="faqForm">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <input type="text" class="form-control" id="question" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Answer</label>
                            <textarea class="form-control" id="answer" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveFaq()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadFaqs() {
            fetch('/api/faq', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('faqs');
                if (Object.keys(data).length > 0) {
                    let html = '';
                    for (const [category, faqs] of Object.entries(data)) {
                        html += `
                            <div class="mb-5">
                                <h5 class="mb-3"><i class="fas fa-folder me-2"></i>${category || 'Uncategorized'}</h5>
                                <div class="accordion accordion-flush">
                        `;
                        faqs.forEach((faq, index) => {
                            html += `
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq${faq.id}">
                                            ${faq.question}
                                        </button>
                                    </h2>
                                    <div id="faq${faq.id}" class="accordion-collapse collapse" data-bs-parent=".accordion">
                                        <div class="accordion-body">
                                            <p>${faq.answer}</p>
                                            <div class="mt-3 d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editFaq(${faq.id}, '${category}', '${faq.question.replace(/'/g, "\\'")}', '${faq.answer.replace(/'/g, "\\'")}')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteFaq(${faq.id})">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += `
                                </div>
                            </div>
                        `;
                    }
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p class="text-muted text-center py-4">No FAQs found. Create one to get started.</p>';
                }
            })
            .catch(error => {
                document.getElementById('faqs').innerHTML = '<p class="text-danger">Error loading FAQs</p>';
            });
        }

        function saveFaq() {
            const data = {
                category: document.getElementById('category').value,
                question: document.getElementById('question').value,
                answer: document.getElementById('answer').value
            };
            
            fetch('/api/faqs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(() => {
                document.getElementById('faqForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('addFaqModal')).hide();
                loadFaqs();
            })
            .catch(error => console.error('Error saving FAQ:', error));
        }

        function editFaq(id, category, question, answer) {
            document.getElementById('category').value = category;
            document.getElementById('question').value = question;
            document.getElementById('answer').value = answer;
            document.getElementById('faqForm').dataset.faqId = id;
            
            const modal = new bootstrap.Modal(document.getElementById('addFaqModal'));
            modal.show();
        }

        function deleteFaq(id) {
            if (confirm('Delete this FAQ?')) {
                fetch(`/api/faqs/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(() => loadFaqs())
                .catch(error => console.error('Error deleting FAQ:', error));
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadFaqs();
        });
    </script>

    @vite(['resources/js/app.js'])
</body>
</html>
