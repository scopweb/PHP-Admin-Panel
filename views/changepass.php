<?php
declare(strict_types=1);
require_once 'init.php';
require_auth();
include 'header.php';
?><body class="bg-dark">

  <div class="container">
    <div class="card card-login mx-auto mt-5">
      <div class="card-header">Change Password</div>
      <div class="card-body">
        <div class="text-center mb-4">
          <h4>Change your password</h4>
          <p class="text-muted small">Password must be at least 8 characters</p>
        </div>

        <form action="../Admin_modules/login_validate.php" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <div class="form-label-group">
              <input type="password" id="old" name="old" class="form-control" placeholder="Old Password" autofocus="autofocus" required>
              <label for="old">Old Password</label>
            </div>
          </div>

          <div class="form-group">
            <div class="form-label-group">
              <input type="password" id="new" name="new" class="form-control" placeholder="New Password" required minlength="8">
              <label for="new">New Password</label>
            </div>
          </div>

          <div class="form-group">
            <div class="form-label-group">
              <input type="password" id="confirm" name="confirm" class="form-control" placeholder="Confirm Password" required minlength="8">
              <label for="confirm">Confirm Password</label>
            </div>
          </div>

          <button class="btn btn-primary btn-block" type="submit" name="change">Change Password</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

</body>

</html>
