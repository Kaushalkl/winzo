<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php'; //connection file
//require_once 'functions.php'; //use for send main phpmailer
require_once 'config.php'; // api integtration test
session_start(); 
//ensureLoggedIn();

$email = $_SESSION['user_email'];
$user = $conn->query("SELECT * FROM users WHERE email='".$conn->real_escape_string($email)."'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wallet App — Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}
body {
    background: linear-gradient(135deg,#43cea2,#185a9d);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: .3s ease;
    overflow-x: hidden;
    color: #fff;
}
body.dark-mode {
    background: linear-gradient(135deg,#121212,#1c1c1c);
}
.wrapper {
    width: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
}
.card {
    width: 100%;
    max-width: 450px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,.25);
    margin-bottom: 20px;
    transition: .3s;
}
.card h2,h5,h6 {
    margin-bottom: 12px;
}
.card img {
    width: 100%;
    border-radius: 12px;
    margin-top: 10px;
}
.btn-custom {
    width: 100%;
    padding: 14px;
    margin-top: 10px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg,#ff416c,#ff4b2b);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
}
.btn-custom:hover {
    transform: translateY(-2px);
}
.btn-custom:disabled {
    background: linear-gradient(135deg,#666,#888);
    cursor: not-allowed;
    transform: none;
}
.navbar {
    width: 100%;
    max-width: 450px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-radius: 20px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    box-shadow: 0 5px 15px rgba(0,0,0,.2);
}
.bottom-nav {
    width: 100%;
    max-width: 450px;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 10px;
    border-radius: 20px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    box-shadow: 0 5px 15px rgba(0,0,0,.2);
    position: fixed;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
}
.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 12px;
    color: #bbb;
    cursor: pointer;
    transition: all 0.3s ease;
}
.nav-item.active {
    background: linear-gradient(135deg,#ff416c,#ff4b2b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 600;
    transform: scale(1.1);
}
.nav-item svg {
    width: 22px;
    height: 22px;
    margin-bottom: 3px;
    transition: all 0.3s ease;
}
.nav-item.active svg {
    filter: drop-shadow(0 0 4px #ff416c) drop-shadow(0 0 4px #ff4b2b);
}
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(3px);
    justify-content: center;
    align-items: center;
    padding: 20px;
    z-index: 1000;
}
.modal.show {
    display: flex;
    animation: fadeIn .3s ease;
}
.modal-content {
    background: rgba(30,30,30,0.95);
    border-radius: 16px;
    padding: 20px;
    width: 100%;
    max-width: 350px;
    text-align: center;
    border: 1px solid #444;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    border-bottom: 1px solid #444;
    padding-bottom: 10px;
}
.modal-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-close:hover {
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}
input[type=number],input[type=text] {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    margin: 10px 0;
    font-size: 16px;
    text-align: center;
    border: none;
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid #444;
}
input[type=number]:focus,input[type=text]:focus {
    outline: none;
    border-color: #ff416c;
    background: rgba(255,255,255,0.15);
}
label {
    display: block;
    text-align: left;
    margin-top: 10px;
    font-weight: 600;
}
.popup-msg {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #222;
    padding: 12px 20px;
    border-radius: 8px;
    color: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.4);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease;
    z-index: 1001;
    border: 1px solid #444;
}
.popup-msg.show {
    opacity: 1;
}
.dark-mode .card {
    background: rgba(30,30,30,0.85);
}
.dark-mode .navbar,.dark-mode .bottom-nav {
    background: rgba(50,50,50,0.85);
}

/* ----- Dark Mode Toggle ----- */
.dark-mode-toggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 10px 14px;
    border: none;
    border-radius: 30px;
    background: #ff416c;
    color: #fff;
    cursor: pointer;
    font-size: .85rem;
    box-shadow: 0 5px 15px rgba(0,0,0,.25);
    transition: .3s;
    z-index: 999;
}
.dark-mode-toggle:hover {
    background: #ff2b2b;
    transform: scale(1.05);
}

/* ----- Transaction List ----- */
#tx-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-y: auto;
    max-height: 80vh; /* Full screen scroll */
    padding: 5px;
}
.tx-card {
    background: rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
}
.badge-credit {
    background: #00ff88;
    color: #000;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
}
.badge-debit {
    background: #ff4d4d;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
}
.loading {
    text-align: center;
    padding: 20px;
    color: #bbb;
}
.loading::after {
    content: '...';
    animation: loading 1.5s infinite;
}
@keyframes loading {
    0%,33%{content:'.';}34%,66%{content:'..';}67%,100%{content:'...';}
}
@keyframes fadeIn {
    from {opacity:0;transform:scale(0.95);}
    to {opacity:1;transform:scale(1);}
}

/* ----- Responsive ----- */
@media (max-width:480px) {
    .card {
        padding: 20px;
    }
    /* Move dark mode toggle inside bottom nav */
    .dark-mode-toggle {
        position: relative;
        bottom: auto;
        right: auto;
        margin: 0;
        padding: 6px 10px;
        font-size: .75rem;
        box-shadow: none;
        border-radius: 12px;
        background: linear-gradient(135deg,#ff416c,#ff4b2b);
    }
    .bottom-nav {
        justify-content: space-around;
        gap: 5px;
    }
    .bottom-nav .dark-mode-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

</style>
</head>
<body>

<div class="navbar">
  <span>💳 Wallet</span>
  <div><?= htmlspecialchars($user['name']); ?></div>
</div>

<!-- HOME -->
<div class="wrapper" id="page-home">
  <div class="card">
    <h6>Wallet Balance</h6>
    <h2 id="balance">₹<?= number_format($user['wallet_balance'], 2); ?></h2>
    <button class="btn-custom" onclick="openModal('rechargeModal')">Recharge</button>
    <button class="btn-custom" onclick="openModal('withdrawModal')">Withdraw</button>
  </div>
  <div class="card">
    <h6>Quick QR</h6>
    <img src="qr_code/generate_qr.php?u=<?= $user['id']; ?>" alt="QR Code" onerror="this.style.display='none'">
  </div>
</div>

<!-- TRANSACTIONS -->
<div class="wrapper" id="page-transactions" style="display:none;">
  <div class="card">
    <h5>Transaction History</h5>
    <div id="tx-list" class="loading">Loading transactions...</div>
  </div>
</div>

<!-- PROFILE -->
<div class="wrapper" id="page-profile" style="display:none;">
  <div class="card" id="profile-card">
    <h5>PROFILE</h5>
    <p><b>Name:</b> <?= htmlspecialchars($user['name']); ?></p>
    <p><b>Username:</b> <?= htmlspecialchars($user['username']); ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($user['email']); ?></p>
    <div id="bank-details">
        <?php if(!empty($user['account_number'])): ?>
            <p><b>Bank Account:</b> ****<?= substr($user['account_number'], -4); ?></p>
            <p><b>Bank Name:</b> <?= htmlspecialchars($user['bank_name'] ?? 'N/A'); ?></p>
            <p><b>Account Holder:</b> <?= htmlspecialchars($user['account_holder'] ?? 'N/A'); ?></p>
        <?php else: ?>
            <p><b>Bank Account:</b> Not Added</p>
        <?php endif; ?>
    </div>
    <button class="btn-custom" onclick="openModal('bankModal')">
        <?= empty($user['account_number']) ? 'Add Bank Account' : 'Update Bank Account'; ?>
    </button>
    <button class="btn-custom" onclick="logout()" style="background:linear-gradient(135deg,#666,#888);">Logout</button>
  </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
  <div class="nav-item active" onclick="showPage('home',this)"><i class="fa fa-home"></i>Home</div>
  <div class="nav-item" onclick="showPage('transactions',this)"><i class="fa fa-list"></i>Transactions</div>
  <div class="nav-item" onclick="showPage('profile',this)"><i class="fa fa-user"></i>Profile</div>
</div>

<!-- MODALS -->
<div class="modal" id="rechargeModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5>Recharge Wallet</h5>
      <button class="modal-close" onclick="closeModal('rechargeModal')">&times;</button>
    </div>
    <form id="rechargeForm">
      <label><b>Amount (₹):</b></label>
      <input type="number" name="amount" placeholder="Enter amount" required min="10" step="1">
      <small style="display:block;text-align:left;margin-top:5px;color:#bbb;">Minimum recharge: ₹10</small>
      <button class="btn-custom" type="submit" id="rechargeBtn">Proceed to Pay</button>
    </form>
  </div>
</div>

<div class="modal" id="withdrawModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5>Withdraw Funds</h5>
      <button class="modal-close" onclick="closeModal('withdrawModal')">&times;</button>
    </div>
    <form id="withdrawForm">
      <label><b>Amount (₹):</b></label>
     <input type="number" id="withdraw_amount" name="amount" class="form-control" min="10" required>

      <small style="display:block;text-align:left;margin-top:5px;color:#bbb;">Minimum withdrawal: ₹10</small>
      <button class="btn-custom" type="submit" id="withdrawBtn">Withdraw</button>
    </form>
  </div>
</div>

<div class="modal" id="bankModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5>Bank Account Details</h5>
      <button class="modal-close" onclick="closeModal('bankModal')">&times;</button>
    </div>
    <form id="bankForm">
      <input type="text" name="bank_name" placeholder="Bank Name" required value="<?= htmlspecialchars($user['bank_name'] ?? ''); ?>">
      <input type="text" name="bank_ifsc" placeholder="Bank IFSC Code" required value="<?= htmlspecialchars($user['bank_ifsc'] ?? ''); ?>" pattern="^[A-Z]{4}0[A-Z0-9]{6}$" title="Enter valid IFSC code (e.g., SBIN0000123)">
      <input type="text" name="account_number" placeholder="Account Number" required value="<?= htmlspecialchars($user['account_number'] ?? ''); ?>" pattern="[0-9]{9,18}" title="Account number should be 9-18 digits">
      <input type="text" name="re_account_number" placeholder="Re-Type Account Number" required pattern="[0-9]{9,18}">
      <input type="text" name="account_holder" placeholder="Account Holder Name" required value="<?= htmlspecialchars($user['account_holder'] ?? ''); ?>">
      <button class="btn-custom" type="submit" id="bankBtn">Save Bank Details</button>
    </form>
  </div>
</div>

<div id="popup" class="popup-msg"></div>

<!-- Dark Mode Toggle -->
<!--<button class="dark-mode-toggle">🌙 Dark Mode</button>-->

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
// ===== Utility Functions =====
function popup(msg, type = 'info') {
    const p = document.getElementById('popup');
    p.innerText = msg;
    p.style.background = (type === 'error' ? '#f44336' : type === 'warning' ? '#ff9800' : '#4CAF50');
    p.classList.add('show');
    setTimeout(() => p.classList.remove('show'), 3000);
}

// ===== Page Navigation =====
function showPage(page, el) {
    document.querySelectorAll('.wrapper').forEach(c => c.style.display = 'none');
    document.getElementById('page-' + page).style.display = 'flex';
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    if (page === 'transactions') refreshTransactions(); 
    if (page === 'home') refreshBalance();
}

// ===== Modals =====
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }
});

// ===== Logout =====
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location = 'logout.php';
    }
}

// ===== Smooth Balance Animation =====
let balanceAnimation = {
    el: document.getElementById('balance'),
    current: parseFloat(document.getElementById('balance').innerText.replace(/[^0-9.]/g, '')) || 0,
    target: null,
    startTime: null,
    duration: 600,
    animating: false
};

function animateBalance(newVal) {
    balanceAnimation.target = parseFloat(newVal);

    if (!balanceAnimation.animating) {
        balanceAnimation.animating = true;
        balanceAnimation.startTime = null;
        requestAnimationFrame(stepBalance);
    }
}

function stepBalance(timestamp) {
    if (!balanceAnimation.startTime) balanceAnimation.startTime = timestamp;
    let progress = (timestamp - balanceAnimation.startTime) / balanceAnimation.duration;
    progress = Math.min(progress, 1);

    let currentValue = balanceAnimation.current + (balanceAnimation.target - balanceAnimation.current) * progress;

    // Format as ₹ currency with commas
    balanceAnimation.el.innerText = '₹' + currentValue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (progress < 1) {
        requestAnimationFrame(stepBalance);
    } else {
        // Finish animation
        balanceAnimation.current = balanceAnimation.target;
        balanceAnimation.el.innerText = '₹' + balanceAnimation.target.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        balanceAnimation.animating = false;

        // If target changed during animation, restart
        if (balanceAnimation.current !== balanceAnimation.target) {
            animateBalance(balanceAnimation.target);
        }
    }
}

// ===== Refresh Balance from Server =====
function refreshBalance() {
    fetch('wallet_action.php?action=balance')
        .then(r => r.json())
        .then(d => {
            if (d.balance !== undefined) {
                const newBalance = parseFloat(d.balance.replace(/,/g, ''));
                if (Math.abs(balanceAnimation.current - newBalance) > 0.01) {
                    animateBalance(newBalance);
                }
            }
        })
        .catch(err => console.error('Balance refresh error:', err));
}

// Auto-refresh every 15 seconds
setInterval(refreshBalance, 15000);
refreshBalance();

// ===== Transactions =====
function refreshTransactions() {
    const txList = document.getElementById('tx-list');
    txList.innerHTML = '<div class="loading">Loading transactions</div>';
    
    fetch('wallet_action.php?action=transactions')
        .then(r => r.json())
        .then(d => {
            txList.innerHTML = '';
            if (d.transactions && d.transactions.length) { 
                d.transactions.forEach(tx => {
                    const txDate = new Date(tx.created_at).toLocaleString();
                    txList.innerHTML += `<div class="tx-card">
                        <small>${txDate}</small>
                        <div><span class="${tx.type === 'credit' ? 'badge-credit' : 'badge-debit'}">${tx.type.toUpperCase()}</span></div>
                        <div style="font-size:1.2rem;font-weight:bold;margin:5px 0;">₹${parseFloat(tx.amount).toFixed(2)}</div>
                        <div style="color:#ccc;font-size:0.9rem;">${tx.remark || 'Transaction'}</div>
                    </div>`;
                });
            } else {
                txList.innerHTML = '<div class="tx-card">No transactions yet.</div>';
            }
        })
        .catch(err => {
            console.error('Transactions error:', err);
            txList.innerHTML = '<div class="tx-card">Error loading transactions.</div>';
        });
}

setInterval(refreshTransactions, 15000);

// ===== Razorpay Recharge =====
document.getElementById('rechargeForm').addEventListener('submit', e => {
    e.preventDefault(); 
    const amount = parseFloat(e.target.amount.value);
    const rechargeBtn = document.getElementById('rechargeBtn');
    
    if (amount < 10) {
        popup('Minimum recharge amount is ₹10', 'error');
        return;
    }

    // Disable button during processing
    rechargeBtn.disabled = true;
    rechargeBtn.textContent = 'Processing...';

    fetch('wallet_action.php?action=create_recharge_order', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'amount=' + encodeURIComponent(amount)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            closeModal('rechargeModal');
            
            const options = {
                key: d.key_id,
                amount: d.amount * 100, // Convert to paise
                currency: d.currency,
                name: d.name,
                description: d.description,
                order_id: d.order_id,
                prefill: d.prefill,
                handler: function(response) {
                    // Verify payment
                    fetch('wallet_action.php?action=verify_payment', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'razorpay_payment_id=' + response.razorpay_payment_id +
                              '&razorpay_order_id=' + response.razorpay_order_id +
                              '&razorpay_signature=' + response.razorpay_signature
                    })
                    .then(r => r.json())
                    .then(result => {
                        if (result.success) {
                            popup(result.message);
                            animateBalance(result.new_balance);
                            refreshTransactions();
                            e.target.reset();
                        } else {
                            popup(result.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Payment verification error:', err);
                        popup('Payment verification failed', 'error');
                    });
                },
                theme: {
                    color: '#ff416c'
                },
                modal: {
                    ondismiss: function() {
                        popup('Payment cancelled', 'warning');
                        rechargeBtn.disabled = false;
                        rechargeBtn.textContent = 'Proceed to Pay';
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
            
        } else {
            popup(d.message, 'error');
            rechargeBtn.disabled = false;
            rechargeBtn.textContent = 'Proceed to Pay';
        }
    })
    .catch(err => {
        console.error('Recharge error:', err);
        popup('Network error. Please try again.', 'error');
        rechargeBtn.disabled = false;
        rechargeBtn.textContent = 'Proceed to Pay';
    });
});

// ===== Withdraw Form =====
document.getElementById('withdrawForm').addEventListener('submit', e => {
    e.preventDefault(); 
    const amount = parseFloat(e.target.amount.value);
    const withdrawBtn = document.getElementById('withdrawBtn');
    const bankCard = document.getElementById('bank-details');
   
   
   
    if (amount < 10) {
    showError("Minimum withdrawal amount is ₹10");
    return;
}


    if (bankCard.innerText.includes('Not Added')) {
        popup('Please add bank account first', 'error');
        closeModal('withdrawModal');
        openModal('bankModal');
        return;
    }

    // Disable button during processing
    withdrawBtn.disabled = true;
    withdrawBtn.textContent = 'Processing...';

    fetch('wallet_action.php?action=withdraw', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'amount=' + encodeURIComponent(amount)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            popup(d.message);
            animateBalance(d.new_balance);
            refreshTransactions();
            closeModal('withdrawModal'); 
            e.target.reset();
        } else {
            popup(d.message, 'error');
        }
        withdrawBtn.disabled = false;
        withdrawBtn.textContent = 'Withdraw';
    })
    .catch(err => {
        console.error('Withdraw error:', err);
        popup('Network error. Please try again.', 'error');
        withdrawBtn.disabled = false;
        withdrawBtn.textContent = 'Withdraw';
    });
});

// ===== Bank Form =====
document.getElementById('bankForm').addEventListener('submit', e => {
    e.preventDefault(); 
    const bankBtn = document.getElementById('bankBtn');
    
    if (e.target.account_number.value !== e.target.re_account_number.value) {
        popup('Account numbers do not match', 'error');
        return;
    }

    // Validate IFSC format
    const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;
    if (!ifscRegex.test(e.target.bank_ifsc.value.toUpperCase())) {
        popup('Please enter a valid IFSC code', 'error');
        return;
    }

    // Disable button during processing
    bankBtn.disabled = true;
    bankBtn.textContent = 'Saving...';

    const formData = new URLSearchParams(new FormData(e.target)).toString();
    fetch('wallet_action.php?action=add_bank', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            popup('Bank details saved successfully!'); 
            closeModal('bankModal');
            document.getElementById('bank-details').innerHTML = `
                <p><b>Bank Account:</b> ****${e.target.account_number.value.slice(-4)}</p>
                <p><b>Bank Name:</b> ${e.target.bank_name.value}</p>
                <p><b>Account Holder:</b> ${e.target.account_holder.value}</p>
            `;
            // Update button text
            document.querySelector('#profile-card .btn-custom').textContent = 'Update Bank Account';
        } else {
            popup(d.message, 'error');
        }
        bankBtn.disabled = false;
        bankBtn.textContent = 'Save Bank Details';
    })
    .catch(err => {
        console.error('Bank form error:', err);
        popup('Network error. Please try again.', 'error');
        bankBtn.disabled = false;
        bankBtn.textContent = 'Save Bank Details';
    });
});

// ===== Dark Mode Toggle =====
const darkToggle = document.querySelector('.dark-mode-toggle');

// Load saved preference
if (localStorage.getItem('dark-mode') === 'true') {
    document.body.classList.add('dark-mode');
    darkToggle.textContent = '☀️ Light Mode';
} else {
    darkToggle.textContent = '🌙 Dark Mode';
}

// Toggle and save preference
darkToggle.addEventListener('click', () => {
    const dark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('dark-mode', dark);
    darkToggle.textContent = dark ? '☀️ Light Mode' : '🌙 Dark Mode';
});

// Initialize transactions on page load
refreshTransactions();
</script>
</body>
</html>








































<script>
// =========================
// Utility
// =========================
function popup(msg,type='info'){
    const p=document.getElementById('popup');
    p.innerText=msg;
    p.style.background=type==='error'?'#f44336':type==='warning'?'#ff9800':'#4CAF50';
    p.classList.add('show');
    setTimeout(()=>p.classList.remove('show'),3000);
}

// =========================
// Page Navigation
// =========================
function showPage(page,el){
    document.querySelectorAll('.wrapper').forEach(c=>c.style.display='none');
    document.getElementById('page-'+page).style.display='flex';
    document.querySelectorAll('.nav-item').forEach(i=>i.classList.remove('active'));
    el.classList.add('active');
    if(page==='transactions') refreshTransactions();
    if(page==='home') refreshBalance();
}

// =========================
// Modals
// =========================
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
document.addEventListener('click',e=>{if(e.target.classList.contains('modal')) e.target.classList.remove('show');});

// =========================
// Logout
// =========================
function logout(){
    if(confirm('Are you sure you want to logout?')) window.location='logout.php';
}

// =========================
// Balance Animation
// =========================
let balanceAnim={el:document.getElementById('balance'),current:parseFloat(document.getElementById('balance').innerText.replace(/[^0-9.]/g,''))||0,target:null,startTime:null,animating:false};
function animateBalance(newVal){
    balanceAnim.target=parseFloat(newVal);
    if(!balanceAnim.animating){balanceAnim.animating=true;balanceAnim.startTime=null;requestAnimationFrame(stepBalance);}
}
function stepBalance(timestamp){
    if(!balanceAnim.startTime) balanceAnim.startTime=timestamp;
    let progress=(timestamp-balanceAnim.startTime)/600;
    progress=Math.min(progress,1);
    let currentVal=balanceAnim.current+(balanceAnim.target-balanceAnim.current)*progress;
    balanceAnim.el.innerText='₹'+currentVal.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
    if(progress<1) requestAnimationFrame(stepBalance);
    else{balanceAnim.current=balanceAnim.target; balanceAnim.el.innerText='₹'+balanceAnim.target.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); balanceAnim.animating=false; if(balanceAnim.current!==balanceAnim.target) animateBalance(balanceAnim.target);}
}

// =========================
// Refresh Balance & Transactions
// =========================
function refreshBalance(){
    fetch('wallet_action.php?action=balance').then(r=>r.json()).then(d=>{
        if(d.balance!==undefined){
            const newBal=parseFloat(d.balance.replace(/,/g,''));
            if(Math.abs(balanceAnim.current-newBal)>0.01) animateBalance(newBal);
        }
    }).catch(err=>console.error(err));
}
setInterval(refreshBalance,15000);

function refreshTransactions(){
    const txList=document.getElementById('tx-list');
    txList.innerHTML='<div class="loading">Loading transactions</div>';
    fetch('wallet_action.php?action=transactions').then(r=>r.json()).then(d=>{
        txList.innerHTML='';
        if(d.transactions && d.transactions.length){
            d.transactions.forEach(tx=>{
                const txDate=new Date(tx.created_at).toLocaleString();
                txList.innerHTML+=`<div class="tx-card"><small>${txDate}</small><div><span class="${tx.type==='credit'?'badge-credit':'badge-debit'}">${tx.type.toUpperCase()}</span></div><div style="font-size:1.2rem;font-weight:bold;margin:5px 0;">₹${parseFloat(tx.amount).toFixed(2)}</div><div style="color:#ccc;font-size:0.9rem;">${tx.remark||'Transaction'}</div></div>`;
            });
        }else txList.innerHTML='<div class="tx-card">No transactions yet.</div>';
    }).catch(err=>{console.error(err);txList.innerHTML='<div class="tx-card">Error loading transactions.</div>';});
}
setInterval(refreshTransactions,15000);

// =========================
// Razorpay Recharge
// =========================
document.getElementById('rechargeForm').addEventListener('submit',e=>{
    e.preventDefault();
    const amount=parseFloat(e.target.amount.value),btn=document.getElementById('rechargeBtn');
    if(amount<10){popup('Minimum recharge ₹10','error'); return;}
    btn.disabled=true; btn.textContent='Processing...';
    fetch('wallet_action.php?action=create_recharge_order',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'amount='+encodeURIComponent(amount)})
    .then(r=>r.json()).then(d=>{
        if(d.success){
            closeModal('rechargeModal');
            const options={key:d.key_id,amount:d.amount*100,currency:d.currency,name:d.name,description:d.description,order_id:d.order_id,prefill:d.prefill,handler:function(response){
                fetch('wallet_action.php?action=verify_payment',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'razorpay_payment_id='+response.razorpay_payment_id+'&razorpay_order_id='+response.razorpay_order_id+'&razorpay_signature='+response.razorpay_signature}).then(r=>r.json()).then(result=>{
                    if(result.success){popup(result.message); animateBalance(result.new_balance); refreshTransactions(); e.target.reset();}
                    else popup(result.message,'error');
                }).catch(err=>{console.error(err);popup('Payment verification failed','error');});
            },theme:{color:'#ff416c'},modal:{ondismiss:function(){popup('Payment cancelled','warning');btn.disabled=false;btn.textContent='Proceed to Pay';}}};
            const rzp=new Razorpay(options); rzp.open();
        }else{popup(d.message,'error'); btn.disabled=false; btn.textContent='Proceed to Pay';}
    }).catch(err=>{console.error(err); popup('Network error','error'); btn.disabled=false; btn.textContent='Proceed to Pay';});
});

// =========================
/// =========================
// Withdraw Form
// =========================
document.getElementById('withdrawForm').addEventListener('submit', e => {
    e.preventDefault();
    const amount = parseFloat(e.target.amount.value),
          btn = document.getElementById('withdrawSubmitBtn');
    const bankCard = document.getElementById('bank-details');

    if(amount < 10){ popup('Minimum withdrawal ₹10','error'); return; }
    if(bankCard.innerText.includes('Not Added')){ 
        popup('Please add bank first','error');
        closeModal('withdrawModal');
        openModal('bankModal');
        return;
    }

    const selMethod = document.querySelector('#withdrawForm input[name="method"]:checked') || {};
    const method = selMethod.value || 'bank';
    const targetInput = document.getElementById('target_bank');
    const target = targetInput ? targetInput.value.trim() : '';

    // UPI validation
    if(method === 'upi'){
        const upiRegex = /^[\w.-]{2,256}@[a-zA-Z]{2,64}$/;
        if(!upiRegex.test(target)){
            popup('Enter a valid UPI ID (e.g., example@bank)','error');
            return;
        }
    }

    // Paytm validation
    if(method === 'paytm'){
        const paytmRegex = /^\d{10}$/;
        if(!paytmRegex.test(target)){
            popup('Enter a valid 10-digit Paytm number','error');
            return;
        }
    }

    btn.disabled = true; 
    btn.textContent = 'Processing...';

    fetch('wallet_action.php?action=withdraw', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: 'amount='+encodeURIComponent(amount)+'&method='+encodeURIComponent(method)+'&target='+encodeURIComponent(target)
    })
    .then(r => r.json())
    .then(d => {
        if(d.success){
            popup(d.message);
            if(d.new_balance !== undefined) animateBalance(d.new_balance);
            else refreshBalance();
            refreshTransactions();
            closeModal('withdrawModal');
            e.target.reset();
        } else popup(d.message,'error');
        btn.disabled = false;
        btn.textContent = 'Withdraw';
    })
    .catch(err => { 
        console.error(err);
        popup('Network error','error');
        btn.disabled = false;
        btn.textContent = 'Withdraw';
    });
});

// =========================
// Bank Form
// =========================
document.getElementById('bankForm').addEventListener('submit', e => {
    e.preventDefault();
    const btn = document.getElementById('bankBtn');

    if(e.target.account_number.value !== e.target.re_account_number.value){
        popup('Account numbers do not match','error'); 
        return;
    }

    const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;
    if(!ifscRegex.test(e.target.bank_ifsc.value.toUpperCase())){
        popup('Invalid IFSC','error'); 
        return;
    }

    btn.disabled = true; 
    btn.textContent = 'Saving...';

    const formData = new URLSearchParams(new FormData(e.target)).toString();
    fetch('wallet_action.php?action=add_bank', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if(d.success){
            popup('Bank details saved!');
            closeModal('bankModal');
            document.getElementById('bank-details').innerHTML = `
                <p><b>Bank Account:</b> ****${e.target.account_number.value.slice(-4)}</p>
                <p><b>Bank Name:</b> ${e.target.bank_name.value}</p>
                <p><b>Account Holder:</b> ${e.target.account_holder.value}</p>`;
            document.querySelector('#profile-card .btn-custom').textContent='Update Bank Account';
        } else popup(d.message,'error');

        btn.disabled = false;
        btn.textContent = 'Save Bank Details';
    })
    .catch(err => { 
        console.error(err); 
        popup('Network error','error'); 
        btn.disabled = false; 
        btn.textContent = 'Save Bank Details';
    });
});

// =========================
// Withdraw Target Placeholder & Auto-fill
function updateWithdrawTargetPlaceholder(){
    const sel = document.querySelector('#withdrawForm input[name="method"]:checked');
    const method = sel ? sel.value : 'bank';
    const targetInput = document.getElementById('target_bank');
    if(!targetInput) return;

    const bankData = document.getElementById('bank-details');
    const savedBank = bankData?.dataset.account || '';
    const savedUpi = bankData?.dataset.upi || '';
    const savedPaytm = bankData?.dataset.paytm || '';

    if(method === 'bank'){
        targetInput.placeholder = 'Account Number (leave empty to use saved account)';
        targetInput.type = 'text';
        targetInput.required = false;
        targetInput.pattern = '.*';
        targetInput.title = '';
        targetInput.value = savedBank;
    }
    else if(method === 'upi'){
        targetInput.placeholder = 'UPI ID (e.g., example@bank)';
        targetInput.type = 'text';
        targetInput.required = true;
        targetInput.pattern = '^[\\w.-]{2,256}@[a-zA-Z]{2,64}$';
        targetInput.title = 'Enter a valid UPI ID (e.g., example@bank)';
        targetInput.value = savedUpi;
    }
    else if(method === 'paytm'){
        targetInput.placeholder = 'Paytm Number (10 digits)';
        targetInput.type = 'tel';
        targetInput.required = true;
        targetInput.pattern = '^\\d{10}$';
        targetInput.title = 'Enter 10-digit Paytm number';
        targetInput.value = savedPaytm;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#withdrawForm input[name="method"]').forEach(r => r.addEventListener('change', updateWithdrawTargetPlaceholder));
    updateWithdrawTargetPlaceholder();
});
refreshTransactions();
refreshBalance();
</script>
</body>
</html>
