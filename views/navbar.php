<nav class="navbar navbar-expand bg-light static-top">

    <a class="navbar-brand mr-1" href="index.php"><img src="img/logo.jpg" style="width: 50%;"></a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
      <i class="fas fa-bars" style="color: #000000; font-size: 25px;"></i>
    </button>

    <!-- Navbar Right -->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
      <div class="input-group">
        <div class="input-group-append">

        </div>
      </div>
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">
      <!-- Action -->

       <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

        <ul class="nav navbar-nav navbar-right">
          <a href="#" onclick="showTooltip()"><i class="fas fa-user fa-fw" style="color: #000000; font-size: 25px;"></i></a>
          <div class="custom_tooltip text-center" id="tooltip" style="visibility: hidden;">
          <ul class="settings_head">
            <li>Welcome, <?= escape($_SESSION['adm_name'] ?? 'User') ?></li>
            <hr>
            <li><a href="changepass.php"><span class="fa fa-repeat"></span>&nbsp;&nbsp;Change Password</a></li>
            <li><a data-toggle="modal" data-target="#logoutModal"><span class="fa fa-sign-out"></span>&nbsp;&nbsp;Sign Out</a></li>
          </ul>
        </div>
        </ul>
      </div><!-- /.navbar-collapse -->

      <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <form action="logout.php" method="post" style="display: inline;">
            <?= csrf_field() ?>
            <button type="submit" name="logout" class="btn btn-primary">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </div>

          <script>
          function showTooltip() {
              var x = document.getElementById("tooltip");
              if (x.style.visibility == "hidden"){
                x.style.visibility = "visible"
              }else{
                x.style.visibility = "hidden"
              }
          }
        </script>
    </ul>

  </nav>
