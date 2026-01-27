<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Payment - {{ $course->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .payment-body {
            padding: 30px;
        }
        .course-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .price-tag {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .payment-method.selected {
            border-color: #667eea;
            background: #e7f0ff;
        }
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
            width: 100%;
            color: white;
            transition: transform 0.2s;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="payment-card">
        <div class="payment-header">
            <i class="fas fa-graduation-cap fa-3x mb-3"></i>
            <h2>Complete Your Enrollment</h2>
            <p class="mb-0">Secure payment for your course</p>
        </div>
        
        <div class="payment-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <div class="course-info">
                <h4><i class="fas fa-book text-primary"></i> {{ $course->title }}</h4>
                <p class="text-muted mb-2">{{ $course->description }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock"></i> Duration: {{ $course->duration }} hours</span>
                    <span><i class="fas fa-map-marker-alt"></i> {{ $course->state_code }}</span>
                </div>
            </div>
            
            <div class="text-center">
                <div class="price-tag" id="originalPrice">
                    ${{ number_format($course->price ?? 29.99, 2) }}
                </div>
                <div id="discountInfo" style="display: none;">
                    <div class="text-muted"><s id="originalPriceStrike">${{ number_format($course->price ?? 29.99, 2) }}</s></div>
                    <div class="text-success"><strong>Discount: -$<span id="discountAmount">0.00</span></strong></div>
                    <div class="price-tag text-success" id="finalPrice">${{ number_format($course->price ?? 29.99, 2) }}</div>
                </div>
            </div>
            
            <!-- Coupon Section -->
            <div class="card mb-4" style="border: 2px dashed #516425;">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-ticket-alt text-success"></i> Have a Coupon Code?</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="couponCode" placeholder="Enter coupon code" maxlength="6" style="text-transform: uppercase;">
                        <button type="button" class="btn btn-success" onclick="applyCoupon()">
                            <i class="fas fa-check"></i> Apply
                        </button>
                    </div>
                    <div id="couponMessage" class="mt-2"></div>
                </div>
            </div>
            
            <form action="{{ route('payment.process') }}" method="POST" id="paymentForm">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="table" value="{{ $table ?? 'florida_courses' }}">
                <input type="hidden" name="amount" value="{{ $course->price ?? 29.99 }}" id="finalAmount">
                <input type="hidden" name="original_amount" value="{{ $course->price ?? 29.99 }}">
                <input type="hidden" name="coupon_code" value="" id="appliedCouponCode">
                <input type="hidden" name="discount_amount" value="0" id="appliedDiscountAmount">
                
                <h5 class="mb-3">Select Payment Method</h5>
                
                <div class="payment-method" onclick="selectPaymentMethod('stripe')">
                    <label class="d-flex align-items-center mb-0">
                        <input type="radio" name="payment_method" value="stripe" required>
                        <div class="flex-grow-1">
                            <strong><i class="fab fa-cc-stripe"></i> Credit/Debit Card</strong>
                            <div class="text-muted small">Pay securely with Stripe</div>
                        </div>
                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                    </label>
                </div>
                
                <div class="payment-method" onclick="selectPaymentMethod('paypal')">
                    <label class="d-flex align-items-center mb-0">
                        <input type="radio" name="payment_method" value="paypal" required>
                        <div class="flex-grow-1">
                            <strong><i class="fab fa-paypal"></i> PayPal</strong>
                            <div class="text-muted small">Pay with your PayPal account</div>
                        </div>
                        <i class="fab fa-paypal fa-2x text-info"></i>
                    </label>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-pay">
                        <i class="fas fa-lock me-2"></i>Complete Payment
                    </button>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Your payment is secure and encrypted
                    </small>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const originalPrice = {{ $course->price ?? 29.99 }};
        let appliedCoupon = null;
        
        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[value="${method}"]`).checked = true;
        }
        
        async function applyCoupon() {
            const couponCode = document.getElementById('couponCode').value.trim().toUpperCase();
            const messageDiv = document.getElementById('couponMessage');
            
            if (!couponCode) {
                showCouponMessage('Please enter a coupon code', 'danger');
                return;
            }
            
            // Show loading state
            const applyBtn = event.target;
            const originalText = applyBtn.innerHTML;
            applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
            applyBtn.disabled = true;
            
            try {
                const response = await fetch('/api/coupons/apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        code: couponCode,
                        amount: originalPrice
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.valid) {
                    appliedCoupon = data.coupon;
                    const discount = parseFloat(data.discount) || 0;
                    const finalAmount = parseFloat(data.final_amount) || 0;
                    
                    if (finalAmount <= 0) {
                        showCouponMessage('Coupon applied! Course is now free. Click Next to complete enrollment.', 'success');
                    } else {
                        showCouponMessage(`Coupon applied! You saved $${discount.toFixed(2)}`, 'success');
                    }
                    
                    updatePriceDisplay(discount, finalAmount);
                    updateHiddenFields(couponCode, discount, finalAmount);
                    
                    // Change button to "Remove"
                    applyBtn.innerHTML = '<i class="fas fa-times"></i> Remove';
                    applyBtn.onclick = removeCoupon;
                    applyBtn.className = 'btn btn-outline-danger';
                } else {
                    showCouponMessage(data.error || 'Invalid coupon code', 'danger');
                    console.error('Coupon error:', data);
                }
            } catch (error) {
                console.error('Coupon error:', error);
                showCouponMessage('Error applying coupon. Please try again.', 'danger');
            } finally {
                if (!appliedCoupon) {
                    applyBtn.innerHTML = originalText;
                }
                applyBtn.disabled = false;
            }
        }
        
        function removeCoupon() {
            appliedCoupon = null;
            updatePriceDisplay(0, originalPrice);
            updateHiddenFields('', 0, originalPrice);
            showCouponMessage('', '');
            
            // Reset button
            const applyBtn = event.target;
            applyBtn.innerHTML = '<i class="fas fa-check"></i> Apply';
            applyBtn.onclick = applyCoupon;
            applyBtn.className = 'btn btn-success';
            
            // Clear coupon code
            document.getElementById('couponCode').value = '';
        }
        
        function updatePriceDisplay(discount, finalAmount) {
            const originalPriceDiv = document.getElementById('originalPrice');
            const discountInfoDiv = document.getElementById('discountInfo');
            
            if (discount > 0) {
                originalPriceDiv.style.display = 'none';
                discountInfoDiv.style.display = 'block';
                document.getElementById('discountAmount').textContent = discount.toFixed(2);
                document.getElementById('finalPrice').textContent = '$' + finalAmount.toFixed(2);
            } else {
                originalPriceDiv.style.display = 'block';
                discountInfoDiv.style.display = 'none';
            }
        }
        
        function updateHiddenFields(couponCode, discount, finalAmount) {
            document.getElementById('finalAmount').value = finalAmount;
            document.getElementById('appliedCouponCode').value = couponCode;
            document.getElementById('appliedDiscountAmount').value = discount;
        }
        
        function showCouponMessage(message, type) {
            const messageDiv = document.getElementById('couponMessage');
            if (message) {
                messageDiv.innerHTML = `<div class="alert alert-${type} alert-sm mb-0">${message}</div>`;
            } else {
                messageDiv.innerHTML = '';
            }
        }
        
        // Auto-uppercase coupon code input
        document.getElementById('couponCode').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
        
        // Allow Enter key to apply coupon
        document.getElementById('couponCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCoupon();
            }
        });
        
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedMethod) {
                e.preventDefault();
                alert('Please select a payment method');
            }
        });
    </script>
</body>
</html>
