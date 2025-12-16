<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WEBUILD Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary bg-opacity-25 min-vh-100 d-flex align-items-center justify-content-center">
  <div class="w-100" style="max-width: 600px;">
    <h1 class="text-center fw-bold mb-4" style="font-family:serif;">WEBUILD</h1>
    <div class="bg-white p-5 rounded-4 shadow-sm mx-auto" style="max-width:400px;">
      
      <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
      <?php endif; ?>
      
      <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
      <?php endif; ?>
      
      <?php if(isset($validation)): ?>
        <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
      <?php endif; ?>

      <form method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <div class="mb-4">
          <label for="email" class="form-label fw-semibold" style="font-family:serif;">Email</label>
          <input type="email" class="form-control rounded-pill bg-secondary bg-opacity-25 border-1" id="email" name="email" required>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label fw-semibold" style="font-family:serif;">Password</label>
          <input type="password" class="form-control rounded-pill bg-secondary bg-opacity-25 border-1" id="password" name="password" required>
        </div>
        <div class="text-center mt-3">
          <button type="submit" class="btn btn-dark rounded-pill px-5">Login</button>
        </div>
        <div class="text-center mt-3">
          <a href="#" class="text-dark fw-semibold" style="font-family:serif;">Forgot password?</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>