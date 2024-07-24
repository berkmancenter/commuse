<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Welcome back<?= $this->endSection() ?>

<?= $this->section('main') ?>

    <div class="container d-flex justify-content-center p-5">
        <div class="card col-12 col-md-5 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Welcome back</h5>

                <div class="mb-4">
                  <?php echo \App\Libraries\SystemSettingsWrapper::getInstance()->getSettingByKey('ReintakeMessage')['value']; ?>
                </div>

                <a href="<?php echo site_url('reintakeDeny') ?>" type="submit" class="btn btn-danger">Decline</a>
                <a href="<?php echo site_url('reintakeAccept') ?>" type="submit" class="btn btn-success float-end">Accept</a>
            </div>
        </div>
    </div>

    <style>
      p {
        margin-bottom: 0;
      }
    </style>
<?= $this->endSection() ?>
