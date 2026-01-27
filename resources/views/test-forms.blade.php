<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Controls Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-check-square"></i> Form Controls Test</h2>
            </div>
            <div class="card-body">
                <form>
                    <h4>Checkboxes</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="checkbox1">
                        <label class="form-check-label" for="checkbox1">
                            Default checkbox
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="checkbox2" checked>
                        <label class="form-check-label" for="checkbox2">
                            Checked checkbox
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="checkbox3" disabled>
                        <label class="form-check-label" for="checkbox3">
                            Disabled checkbox
                        </label>
                    </div>

                    <hr class="my-4">

                    <h4>Radio Buttons</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radioGroup" id="radio1" value="option1">
                        <label class="form-check-label" for="radio1">
                            First radio
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radioGroup" id="radio2" value="option2" checked>
                        <label class="form-check-label" for="radio2">
                            Second radio (checked)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radioGroup" id="radio3" value="option3">
                        <label class="form-check-label" for="radio3">
                            Third radio
                        </label>
                    </div>

                    <hr class="my-4">

                    <h4>Gender Selection (like registration)</h4>
                    <div class="radio-group d-flex gap-3">
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="gender" value="male">
                            <span class="form-check-label">Male</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="gender" value="female">
                            <span class="form-check-label">Female</span>
                        </label>
                    </div>

                    <hr class="my-4">

                    <h4>Survey Style Questions</h4>
                    <div class="mb-3">
                        <label class="form-label fw-bold">How would you rate this course?</label>
                        <div class="d-flex gap-4 mt-3">
                            <div class="form-check">
                                <input type="radio" name="rating" value="Yes" id="rating_yes" class="form-check-input">
                                <label class="form-check-label" for="rating_yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="rating" value="No" id="rating_no" class="form-check-input">
                                <label class="form-check-label" for="rating_no">No</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4>Admin Style Checkboxes</h4>
                    <div class="form-check">
                        <input type="checkbox" name="is_required" value="1" class="form-check-input" id="isRequired">
                        <label class="form-check-label" for="isRequired">
                            Required (students must complete before certificate)
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked class="form-check-input" id="isActive">
                        <label class="form-check-label" for="isActive">
                            Active
                        </label>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-primary">Test Button</button>
                        <button type="button" class="btn btn-secondary">Secondary Button</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>