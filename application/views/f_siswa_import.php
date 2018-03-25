<div class="row col-md-12 ini_bodi">
    <div class="panel panel-info">
        <div class="panel-heading">Import Data Siswa
        </div>
        <div class="panel-body">
            <form name="f_siswa" action="<?php echo base_url(); ?>import/siswa" id="f_siswa" enctype="multipart/form-data" method="post">
                <input type="hidden" name="id" id="id" value="0">
                <table class="table table-form">
                    <tr><td style="width: 25%">File</td><td style="width: 75%"><input type="file" class="form-control col-md-3" name="import_excel" required></td></tr>
                    <tr><td></td><td>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Simpan</button>
                        <a href="<?php echo base_url(); ?>adm/m_siswa" class="btn btn-default"><i class="fa fa-minus-circle"></i> Kembali</a>
                    </td></tr>
                </table>
            </form>
        </div>
    </div>
</div>
