<?php
require_once 'db.php';
require_once 'functions.php';
session_start();
ensureLoggedIn();

$action = $_GET['action'] ?? 'recharge';
$user = $conn->query("SELECT * FROM users WHERE id='".intval($_SESSION['user_id'])."'")->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo ucfirst($action); ?> Wallet</title>
<link href="css/style.css" rel="stylesheet">
<style>
body { background: #121212; color: #fff; font-family: 'Segoe UI', sans-serif; margin: 0; }
.card { background: rgba(255,255,255,0.05); border-radius: 20px; padding: 20px; max-width: 480px; margin: 40px auto; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
h2 { text-align: center; margin-bottom: 20px; }
.input, .btn-primary { width: 100%; padding: 12px; margin: 8px 0; border-radius: 10px; border: none; font-size: 16px; }
.input { background: rgba(255,255,255,0.1); color: #fff; }
.btn-primary { background: #4CAF50; color: #fff; font-weight: bold; cursor: pointer; transition: background 0.2s; }
.btn-primary:hover { background: #43a047; }
.tx-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
.tx-table th, .tx-table td { padding: 10px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
.tx-credit { color: #4CAF50; font-weight: 600; }
.tx-debit { color: #f44336; font-weight: 600; }
.no-tx { text-align: center; padding: 10px; opacity: 0.7; }
.balance { font-size: 2rem; font-weight: bold; text-align: center; margin-bottom: 10px; }
.back-link { display: block; text-align: center; margin-top: 15px; color: #90caf9; }
.alert { text-align: center; padding: 10px; margin: 10px 0; border-radius: 10px; }
.alert-success { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
.alert-error { background: rgba(244, 67, 54, 0.2); color: #f44336; }

/* ===== Bank Modal Styles ===== */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(3px); justify-content:center; align-items:center; padding:20px; }
.modal.show{display:flex; animation:fadeIn 0.3s ease;}
@keyframes fadeIn{from{opacity:0;transform:scale(0.95);}to{opacity:1;transform:scale(1);}}
.modal-content { background:rgba(20,20,20,0.95); border-radius:16px; padding:20px; width:100%; max-width:350px; }
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
.modal-close{background:none;border:none;color:#fff;font-size:22px;cursor:pointer}
</style>
</head>
<body>

<div class="card">
  <h2><?php echo ucfirst($action); ?> Wallet</h2>

  <!-- Wallet Balance -->
  <div class="balance" id="wallet-balance">₹<?php echo number_format($user['wallet_balance'], 2); ?></div>

  <!-- Alert messages -->
  <div id="alert-box"></div>

  <!-- Recharge/Withdraw Form -->
  <form id="walletForm">
    <input type="number" step="0.01" name="amount" class="input" placeholder="Enter amount" required>
    <button type="submit" class="btn-primary"><?php echo ucfirst($action); ?></button>
  </form>

  <!-- Bank Update Button -->
  <button class="btn-primary" onclick="openModal('bankModal')">Add / Update Bank</button>

  <!-- Back Link -->
  <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

  <!-- Recent Transactions -->
  <h3 style="text-align:center; margin-top:20px;">Recent Transactions</h3>
  <div id="tx-section">
    <div class="no-tx">Loading...</div>
  </div>
</div>

<!-- Bank Modal -->
<div class="modal" id="bankModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5>Add / Update Bank Account</h5>
      <button class="modal-close" onclick="closeModal('bankModal')">&times;</button>
    </div>
    <form id="bankForm">
      <input type="text" name="bank_name" placeholder="Bank Name" required value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>">
      <input type="text" name="bank_ifsc" placeholder="Bank IFSC Code" required value="<?php echo htmlspecialchars($user['bank_ifsc'] ?? ''); ?>">
      <input type="text" name="account_number" placeholder="Account Number" required value="<?php echo htmlspecialchars($user['account_number'] ?? ''); ?>">
      <input type="text" name="re_account_number" placeholder="Re-Type Account Number" required>
      <input type="text" name="account_holder" placeholder="Account Holder Name" required value="<?php echo htmlspecialchars($user['account_holder'] ?? ''); ?>">
      <button class="btn-primary" type="submit">Save</button>
    </form>
  </div>
</div>

<script>
// ===== Animate Balance =====
function animateBalance(newVal){
  const el = document.getElementById('wallet-balance');
  let current = parseFloat(el.innerText.replace('₹','')) || 0;
  let start=current, end=parseFloat(newVal), duration=800, startTime=null;
  function step(ts){
    if(!startTime) startTime=ts;
    let progress=ts-startTime;
    let val=start+(end-start)*Math.min(progress/duration,1);
    el.innerText='₹'+val.toFixed(2);
    if(progress<duration) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

// ===== Show Alert =====
function showAlert(msg, type='success'){
  const box=document.getElementById('alert-box');
  box.innerHTML=`<div class="alert alert-${type==='success'?'success':'error'}">${msg}</div>`;
  setTimeout(()=>box.innerHTML='',3000);
}

// ===== Fetch Transactions =====
function loadTransactions(){
  fetch('ajax.php?action=transactions')
    .then(r=>r.json())
    .then(data=>{
      const sec=document.getElementById('tx-section');
      if(data.transactions && data.transactions.length){
        let html='<table class="tx-table"><tr><th>Date</th><th>Type</th><th>Amount</th><th>Remark</th></tr>';
        data.transactions.slice(0,5).forEach(tx=>{
          html+=`<tr>
            <td>${tx.created_at}</td>
            <td class="${tx.type==='credit'?'tx-credit':'tx-debit'}">${tx.type}</td>
            <td>₹${parseFloat(tx.amount).toFixed(2)}</td>
            <td>${tx.remark}</td>
          </tr>`;
        });
        html+='</table>';
        sec.innerHTML=html;
      } else {
        sec.innerHTML='<div class="no-tx">No transactions yet.</div>';
      }
    });
}
loadTransactions();

// ===== Handle Wallet Form Submit =====
document.getElementById('walletForm').addEventListener('submit',function(e){
  e.preventDefault();
  let amt=this.amount.value;
  fetch('wallet_action.php?action=<?php echo $action; ?>',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'amount='+encodeURIComponent(amt)
  }).then(r=>r.json()).then(data=>{
    if(data.success){
      showAlert(data.message,'success');
      animateBalance(data.new_balance);
      loadTransactions();
      this.reset();
    } else {
      showAlert(data.message,'error');
    }
  });
});

// ===== Handle Bank Form Submit =====
document.getElementById('bankForm').addEventListener('submit',function(e){
  e.preventDefault();
  const formData = new URLSearchParams(new FormData(this)).toString();
  fetch('wallet_action.php?action=add_bank',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: formData
  }).then(r=>r.json()).then(data=>{
    if(data.success){
      showAlert('Bank details updated successfully!','success');
      closeModal('bankModal');
      location.reload();
    } else {
      showAlert(data.message,'error');
    }
  });
});

// ===== Modal Controls =====
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
</script>

</body>
</html>
