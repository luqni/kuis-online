<div class="row col-md-12 ini_bodi">
  <div class="panel panel-info">
    <div class="panel-heading">Daftar Ujian / Tes</div>
    <div class="panel-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th width="20%">Nama Tes</th>
              <th width="20%">Mapel / Guru</th>
              <th width="10%">Jumlah Soal</th>
              <th width="10%">Waktu</th>
              <th width="10%">Status</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <?php 
              if (!empty($data)) {
                $no = 1;
                foreach ($data as $d) {
                  
                  echo '<tr>
                        <td class="ctr">'.$no.'</td>
                        <td>'.$d->nama_ujian.'</td>
                        <td>'.$d->nmmapel .' ('.$d->nmguru.')</td>
                        <td class="ctr">'.$d->jumlah_soal.'</td>
                        <td class="ctr">'.$d->waktu.' menit</td>
                        <td class="ctr">'.$d->status.'</td>
                        <td class="ctr">';

                  if ($d->status == "Belum Ikut") {
                   echo '<a href="'.base_url().'adm/ikut_ujian/token/'.$d->id.'" target="_blank" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Ikuti Ujian</a>';
                  } else if ($d->status == "Sedang Tes") {
                    echo '<a href="'.base_url().'adm/ikut_ujian/token/'.$d->id.'" target="_blank" class="btn btn-success btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp; <blink>Ujian Sdg Aktif</blink></a>';
                  } else if ($d->status == "Waktu Habis") {
                    echo '<a href="'.base_url().'adm/ikut_ujian/token/'.$d->id.'" target="_blank" class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp; <blink>Waktu Habis</blink></a>';
                  } else {
                    echo '<a href="'.base_url().'adm/sudah_selesai_ujian/'.$d->id.'" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-ok" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Anda sudah ikut</a>';
                  }

                  echo '</td></tr>';
                $no++;
                }
              } else {
                echo '<tr><td colspan="7">Belum ada data</td></tr>';
              }
            ?>
          </tbody>
        </table>

       </div>
    </div>
  </div>
</div>
