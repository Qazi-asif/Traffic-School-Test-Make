<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6c757d;
            font-size: 16px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle:hover {
            color: #0d6efd;
        }
        .password-wrapper input {
            padding-right: 45px;
        }
    </style>
</head>
<body>
    <?php if (isset($component)) { $__componentOriginal785aa89cb5e4b7a0bdd2387b4230b4ed = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal785aa89cb5e4b7a0bdd2387b4230b4ed = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('theme-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal785aa89cb5e4b7a0bdd2387b4230b4ed)): ?>
<?php $attributes = $__attributesOriginal785aa89cb5e4b7a0bdd2387b4230b4ed; ?>
<?php unset($__attributesOriginal785aa89cb5e4b7a0bdd2387b4230b4ed); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal785aa89cb5e4b7a0bdd2387b4230b4ed)): ?>
<?php $component = $__componentOriginal785aa89cb5e4b7a0bdd2387b4230b4ed; ?>
<?php unset($__componentOriginal785aa89cb5e4b7a0bdd2387b4230b4ed); ?>
<?php endif; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if(session('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- <div class="alert alert-warning mb-3">
                            <div class="text-center">
                                <strong style="font-size: 18px;">Note: The Login ID and Password are <u>BOTH Case Sensitive</u></strong>
                            </div>
                        </div> -->
                        
                        <!-- <div class="alert alert-info mb-3">
                            <strong>Password Guidelines:</strong><br>
                            <small class="d-block mt-2">
                                <strong>Note: Your Password must meet the following criteria:</strong><br>
                                1) at least eight characters in length<br>
                                2) contain upper and lower case characters<br>
                                3) contain at least one numeric character<br>
                                4) contain at least one special character.<br>
                                Acceptable special characters are: ! @ # $ & * ( )<br><br>
                                <strong>It is recommended that your Password meet the following criteria:</strong><br>
                                5) does not contain words found in a dictionary<br>
                                6) should not contain names of pets, family, etc.<br>
                                7) does not match a previous password
                            </small>
                        </div> -->
                        
                        <form method="POST" action="/login">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo e(old('email')); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small><a href="<?php echo e(route('password.request')); ?>">Forgot your password?</a></small>
                        </div>
                        
                        <div class="mt-2 text-center">
                            <small>Don't have an account? <a href="https://www.1dummiestrafficschool.com/">Register here</a></small>
                        </div>  
                        
                        </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/csrf-handler.js"></script>
    <script>
        // Password toggle functionality
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
<?php /**PATH D:\laragon\www\nelly-elearning\resources\views/login.blade.php ENDPATH**/ ?>