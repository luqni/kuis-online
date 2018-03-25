<div class="row col-md-12 ini_bodi">
  <div class="panel panel-info">
    <div class="panel-heading">Daftar Ujian / Tes
      <div class="tombol-kanan">
        <a class="btn btn-success btn-sm tombol-kanan" href="#" onclick="return m_ujian_e(0);"><i class="glyphicon glyphicon-plus"></i> &nbsp;&nbsp;Tambah</a>
      </div>
    </div>
    <div class="panel-body">


      <table class="table table-bordered" id="datatabel">
        <thead>
          <tr>
            <th width="5%">No</th>
            <th width="20%">Nama Tes</th>
            <th width="20%">Mata Pelajaran</th>
            <th width="10%">Jumlah Soal</th>
            <th width="15%">Waktu</th>
            <th width="15%">Pengacakan Soal</th>
            <th width="15%">Aksi</th>
          </tr>
        </thead>

        <tbody></tbody>
      </table>
    
      </div>
    </div>
  </div>
</div>
                    




<div class="modal fade" id="m_ujian" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 id="myModalLabel">Buat Ujian</h4>
      </div>
      <div class="modal-body">
          <form name="f_ujian" id="f_ujian" onsubmit="return m_ujian_s();">
            <input type="hidden" name="id" id="id" value="0">
            <input type="hidden" name="jumlah_soal1" id="jumlah_soal1" value="0">
              <table class="table table-form">
                <tr><td style="width: 25%">Nama Ujian</td><td style="width: 75%"><input type="text" class="form-control" name="nama_ujian" id="nama_ujian" required></td></tr>
                <tr><td>Mata Pelajaran</td><td><?php echo form_dropdown('mapel', $p_mapel, '', 'onchange="return __ambil_jumlah_soal(this.value);" class="form-control"  id="mapel" required'); ?></td></tr>
                <tr><td>Jumlah soal</td><td><?php echo form_input('jumlah_soal', '', 'class="form-control"  id="jumlah_soal" required'); ?></td></tr>
                <tr><td>Tgl Mulai</td><td>
                  <input type="date" name='tgl_mulai' class="form-control" style="width: 150px; display: inline; float: left" id="tgl_mulai" placeholder="Tgl" required>
                  <input type="time" name='wkt_mulai' class="form-control" style="width: 100px; display: inline; float: left" id="wkt_mulai" placeholder="Waktu" required>
                </td></tr>
                <tr><td>Waktu</td><td><?php echo form_input('waktu', '', 'class="form-control" id="waktu" placeholder="menit" required style="width: 100px; display: inline; float: left"'); ?> <div style="float: left; margin: 4px 0 0 10px"> menit</div></td></tr>
                <tr><td>Keterlambatan</td><td><?php echo form_input('terlambat', '', 'class="form-control" id="terlambat" placeholder="menit" required style="width: 100px; display: inline; float: left"'); ?> <div style="float: left; margin: 4px 0 0 10px"> menit</div></td></tr>
                <tr><td>Acak Soal</td><td><?php echo form_dropdown('acak', $pola_tes, '', 'class="form-control"  id="acak" required'); ?></td></tr>
              </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Simpan</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true"><i class="fa fa-minus-circle"></i> Tutup</button>
      </div>
        </form>
    </div>
  </div>
</div>
