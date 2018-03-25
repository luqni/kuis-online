<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");
class Adm extends CI_Controller {
	function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+7:00'");
        $waktu_sql = $this->db->query("SELECT NOW() AS waktu")->row_array();
        $this->waktu_sql = $waktu_sql['waktu'];
        $this->opsi = array("a","b","c","d","e");
	}
	
	public function get_servertime() {
		$now = new DateTime(); 
        $dt = $now->format("M j, Y H:i:s O"); 

        j($dt);
	}

	public function cek_aktif() {
		if ($this->session->userdata('admin_valid') == false && $this->session->userdata('admin_id') == "") {
			redirect('adm/login');
		} 
	}
	
	public function index() {
		$this->cek_aktif();
		
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		
		$a['p']			= "v_main";
		
		$this->load->view('aaa', $a);
	}
	
	/* == ADMIN == */
	public function m_siswa() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();
		//$a['data'] = $this->db->query("")->result();
		
		if ($uri3 == "det") {
			$a = $this->db->query("SELECT * FROM m_siswa WHERE id = '$uri4'")->row();
			j($a);
			exit();
		} else if ($uri3 == "simpan") {
			$ket 	= "";
			if ($p->id != 0) {
				$this->db->query("UPDATE m_siswa SET nama = '".bersih($p,"nama")."', nim = '".bersih($p,"nim")."', jurusan = '".bersih($p,"jurusan")."'	WHERE id = '".bersih($p,"id")."'");
				$ket = "edit";
			} else {
				$ket = "tambah";
				$this->db->query("INSERT INTO m_siswa VALUES (null, '".bersih($p,"nama")."', '".bersih($p,"nim")."', '".bersih($p,"jurusan")."')");
			}
			
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= $ket." sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "hapus") {
			$this->db->query("DELETE FROM m_siswa WHERE id = '".$uri4."'");
			$this->db->query("DELETE FROM m_admin WHERE level = 'siswa' AND kon_id = '".$uri4."'");			
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= "hapus sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "user") {
			$det_user = $this->db->query("SELECT id, nim FROM m_siswa WHERE id = '$uri4'")->row();

			if (!empty($det_user)) {
				$q_cek_username = $this->db->query("SELECT id FROM m_admin WHERE username = '".$det_user->nim."' AND level = 'siswa'")->num_rows();

				if ($q_cek_username < 1) {

					$this->db->query("INSERT INTO m_admin VALUES (null, '".$det_user->nim."', md5('".$det_user->nim."'), 'siswa', '".$det_user->id."')");
					$ret_arr['status'] 	= "ok";
					$ret_arr['caption']	= "tambah user sukses";
					j($ret_arr);
				} else {
					$ret_arr['status'] 	= "gagal";
					$ret_arr['caption']	= "Username telah digunakan";
					j($ret_arr);					
				}
			} else {
				$ret_arr['status'] 	= "gagal";
				$ret_arr['caption']	= "tambah user gagal";
				j($ret_arr);
			}
			exit();
		} else if ($uri3 == "user_reset") {
			$det_user = $this->db->query("SELECT id, nim FROM m_siswa WHERE id = '$uri4'")->row();

			$this->db->query("UPDATE m_admin SET password = md5('".$det_user->nim."') WHERE level = 'siswa' AND kon_id = '".$det_user->id."'");

			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= "Update password sukses";
			j($ret_arr);

			exit();
		} else if ($uri3 == "ambil_matkul") {
			$matkul = $this->db->query("SELECT m_mapel.*,
										(SELECT COUNT(id) FROM tr_siswa_mapel WHERE id_siswa = ".$uri4." AND id_mapel = m_mapel.id) AS ok
										FROM m_mapel
										")->result();
			$ret_arr['status'] = "ok";
			$ret_arr['data'] = $matkul;
			j($ret_arr);
			exit;
		} else if ($uri3 == "simpan_matkul") {
			$ket 	= "";
			//echo var_dump($p);
			$ambil_matkul = $this->db->query("SELECT id FROM m_mapel ORDER BY id ASC")->result();
			if (!empty($ambil_matkul)) {
				foreach ($ambil_matkul as $a) {
					$p_sub = "id_mapel_".$a->id;
					if (!empty($p->$p_sub)) {
						
						$cek_sudah_ada = $this->db->query("SELECT id FROM tr_siswa_mapel WHERE  id_siswa = '".$p->id_mhs."' AND id_mapel = '".$a->id."'")->num_rows();
						
						if ($cek_sudah_ada < 1) {
							$this->db->query("INSERT INTO tr_siswa_mapel VALUES (null, '".$p->id_mhs."', '".$a->id."')");
						} else {
							$this->db->query("UPDATE tr_siswa_mapel SET id_mapel = '".$p->$p_sub."' WHERE id_siswa = '".$p->id_mhs."' AND id_mapel = '".$a->id."'");
						}
					} else {
						//echo "0<br>";
						$this->db->query("DELETE FROM tr_siswa_mapel WHERE id_siswa = '".$p->id_mhs."' AND id_mapel = '".$a->id."'");
					}
				}
			}
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= $ket." sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "data") {
			$start = $this->input->post('start');
	        $length = $this->input->post('length');
	        $draw = $this->input->post('draw');
	        $search = $this->input->post('search');

	        $d_total_row = $this->db->query("SELECT id FROM m_siswa a WHERE a.nama LIKE '%".$search['value']."%'")->num_rows();
	    
	        $q_datanya = $this->db->query("SELECT a.*,
											(SELECT COUNT(id) FROM m_admin WHERE level = 'siswa' AND kon_id = a.id) AS ada
											FROM m_siswa a
	                                        WHERE a.nama LIKE '%".$search['value']."%' ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();
	        $data = array();
	        $no = ($start+1);

	        foreach ($q_datanya as $d) {
	            $data_ok = array();
	            $data_ok[0] = $no++;
	            $data_ok[1] = $d['nama'];
	            $data_ok[2] = $d['nim'];
	            $data_ok[3] = $d['jurusan'];



	            $data_ok[4] = '<div class="btn-group">
                          <a href="#" onclick="return m_siswa_e('.$d['id'].');" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Edit</a>
                          <a href="#" onclick="return m_siswa_h('.$d['id'].');" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-remove" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Hapus</a>
                         ';

                if ($d['ada'] == "0") {
                  $data_ok[4] .= '<a href="#" onclick="return m_siswa_u('.$d['id'].');" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-user" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Aktifkan User</a>';
                } else {
                  $data_ok[4] .= '<a href="#" onclick="return m_siswa_ur('.$d['id'].');" class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-random" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Reset Password</a>';
                }

	            $data[] = $data_ok;
	        }

	        $json_data = array(
	                    "draw" => $draw,
	                    "iTotalRecords" => $d_total_row,
	                    "iTotalDisplayRecords" => $d_total_row,
	                    "data" => $data
	                );
	        j($json_data);
	        exit;
		} else if ($uri3 == "import") {
			$a['p']	= "f_siswa_import";
		} else {
			$a['p']	= "m_siswa";
		}
		$this->load->view('aaa', $a);
	}
	public function m_guru() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();
		/*
		$a['data'] = $this->db->query("SELECT m_guru.*,
									(SELECT COUNT(id) FROM m_admin WHERE level = 'guru' AND kon_id = m_guru.id) AS ada
									FROM m_guru")->result();
		*/

		if ($uri3 == "det") {
			$a = $this->db->query("SELECT * FROM m_guru WHERE id = '$uri4'")->row();
			j($a);
			exit();
		} else if ($uri3 == "simpan") {
			$ket 	= "";
			if ($p->id != 0) {
				$this->db->query("UPDATE m_guru SET nama = '".bersih($p,"nama")."', nip = '".bersih($p,"nip")."' WHERE id = '".bersih($p,"id")."'");
				$ket = "edit";
			} else {
				$ket = "tambah";
				$this->db->query("INSERT INTO m_guru VALUES (null, '".bersih($p,"nip")."', '".bersih($p,"nama")."')");
			}
			
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= $ket." sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "hapus") {
			$this->db->query("DELETE FROM m_guru WHERE id = '".$uri4."'");
			$this->db->query("DELETE FROM m_admin WHERE level = 'guru' AND kon_id = '".$uri4."'");
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= "hapus sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "user") {
			$det_user = $this->db->query("SELECT id, nip FROM m_guru WHERE id = '$uri4'")->row();

			if (!empty($det_user)) {
				$q_cek_username = $this->db->query("SELECT id FROM m_admin WHERE username = '".$det_user->nip."' AND level = 'guru'")->num_rows();

				if ($q_cek_username < 1) {

					$this->db->query("INSERT INTO m_admin VALUES (null, '".$det_user->nip."', md5('".$det_user->nip."'), 'guru', '".$det_user->id."')");
					$ret_arr['status'] 	= "ok";
					$ret_arr['caption']	= "tambah user sukses";
					j($ret_arr);
				} else {
					$ret_arr['status'] 	= "gagal";
					$ret_arr['caption']	= "Username telah digunakan";
					j($ret_arr);					
				}
			} else {
				$ret_arr['status'] 	= "gagal";
				$ret_arr['caption']	= "tambah user gagal";
				j($ret_arr);
			}
			exit();
		} else if ($uri3 == "user_reset") {
			$det_user = $this->db->query("SELECT id, nip FROM m_guru WHERE id = '$uri4'")->row();

			$this->db->query("UPDATE m_admin SET password = md5('".$det_user->nip."') WHERE level = 'guru' AND kon_id = '".$det_user->id."'");

			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= "Update password sukses";
			j($ret_arr);

			exit();
		} else if ($uri3 == "ambil_matkul") {
			$matkul = $this->db->query("SELECT m_mapel.*,
										(SELECT COUNT(id) FROM tr_guru_mapel WHERE id_guru = ".$uri4." AND id_mapel = m_mapel.id) AS ok
										FROM m_mapel
										")->result();
			$ret_arr['status'] = "ok";
			$ret_arr['data'] = $matkul;
			j($ret_arr);
			exit;
		} else if ($uri3 == "simpan_matkul") {
			$ket 	= "";
			//echo var_dump($p);
			$ambil_matkul = $this->db->query("SELECT id FROM m_mapel ORDER BY id ASC")->result();
			if (!empty($ambil_matkul)) {
				foreach ($ambil_matkul as $a) {
					$p_sub = "id_mapel_".$a->id;
					if (!empty($p->$p_sub)) {
						
						$cek_sudah_ada = $this->db->query("SELECT id FROM tr_guru_mapel WHERE  id_guru = '".$p->id_mhs."' AND id_mapel = '".$a->id."'")->num_rows();
						
						if ($cek_sudah_ada < 1) {
							$this->db->query("INSERT INTO tr_guru_mapel VALUES (null, '".$p->id_mhs."', '".$a->id."')");
						} else {
							$this->db->query("UPDATE tr_guru_mapel SET id_mapel = '".$p->$p_sub."' WHERE id_guru = '".$p->id_mhs."' AND id_mapel = '".$a->id."'");
						}
					} else {
						//echo "0<br>";
						$this->db->query("DELETE FROM tr_guru_mapel WHERE id_guru = '".$p->id_mhs."' AND id_mapel = '".$a->id."'");
					}
				}
			}
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= $ket." sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "data") {
			$start = $this->input->post('start');
	        $length = $this->input->post('length');
	        $draw = $this->input->post('draw');
	        $search = $this->input->post('search');

	        $d_total_row = $this->db->query("SELECT id FROM m_guru a WHERE a.nama LIKE '%".$search['value']."%'")->num_rows();
	    
	        $q_datanya = $this->db->query("SELECT a.*,
											(SELECT COUNT(id) FROM m_admin WHERE level = 'guru' AND kon_id = a.id) AS ada
											FROM m_guru a
	                                        WHERE a.nama LIKE '%".$search['value']."%' ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();
	        $data = array();
	        $no = ($start+1);

	        foreach ($q_datanya as $d) {
	            $data_ok = array();
	            $data_ok[0] = $no++;
	            $data_ok[1] = $d['nama'];
	            $data_ok[2] = $d['nip'];
	            $data_ok[3] = '<div class="btn-group">
                          <a href="#" onclick="return m_guru_e('.$d['id'].');" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Edit</a>
                          <a href="#" onclick="return m_guru_h('.$d['id'].');" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-remove" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Hapus</a>
                          <a href="#" onclick="return m_guru_matkul('.$d['id'].');" class="btn btn-success btn-xs"><i class="glyphicon glyphicon-th-list" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;MaPel Diampu</a>
                         ';

                if ($d['ada'] == "0") {
                  $data_ok[3] .= '<a href="#" onclick="return m_guru_u('.$d['id'].');" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-user" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Aktif User</a>';
                } else {
                  $data_ok[3] .= '<a href="#" onclick="return m_guru_ur('.$d['id'].');" class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-random" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Reset Pass</a>';
                }

	            $data[] = $data_ok;
	        }

	        $json_data = array(
	                    "draw" => $draw,
	                    "iTotalRecords" => $d_total_row,
	                    "iTotalDisplayRecords" => $d_total_row,
	                    "data" => $data
	                );
	        j($json_data);
	        exit;
		} else if ($uri3 == "import") {
			$a['p']	= "f_guru_import";
		} else {
			$a['p']	= "m_guru";
		}
		$this->load->view('aaa', $a);
	}
	public function m_mapel() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();
		$a['data'] = $this->db->query("SELECT m_mapel.* FROM m_mapel")->result();
		if ($uri3 == "det") {
			$a = $this->db->query("SELECT * FROM m_mapel WHERE id = '$uri4'")->row();
			j($a);
			exit();
		} else if ($uri3 == "simpan") {
			$ket 	= "";
			if ($p->id != 0) {
				$this->db->query("UPDATE m_mapel SET nama = '".bersih($p,"nama")."'
								WHERE id = '".bersih($p,"id")."'");
				$ket = "edit";
			} else {
				$ket = "tambah";
				$this->db->query("INSERT INTO m_mapel VALUES (null, '".bersih($p,"nama")."')");
			}
			
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= $ket." sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "hapus") {
			$this->db->query("DELETE FROM m_mapel WHERE id = '".$uri4."'");
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= "hapus sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "data") {
			$start = $this->input->post('start');
	        $length = $this->input->post('length');
	        $draw = $this->input->post('draw');
	        $search = $this->input->post('search');

	        $d_total_row = $this->db->query("SELECT id FROM m_mapel a WHERE a.nama LIKE '%".$search['value']."%'")->num_rows();
	    
	        $q_datanya = $this->db->query("SELECT a.*
											FROM m_mapel a
	                                        WHERE a.nama LIKE '%".$search['value']."%' ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();
	        $data = array();
	        $no = ($start+1);

	        foreach ($q_datanya as $d) {
	            $data_ok = array();
	            $data_ok[0] = $no++;
	            $data_ok[1] = $d['nama'];
	            $data_ok[2] = '<div class="btn-group">
                          <a href="#" onclick="return m_mapel_e('.$d['id'].');" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Edit</a>
                          <a href="#" onclick="return m_mapel_h('.$d['id'].');" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-remove" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Hapus</a>
                         ';

	            $data[] = $data_ok;
	        }

	        $json_data = array(
	                    "draw" => $draw,
	                    "iTotalRecords" => $d_total_row,
	                    "iTotalDisplayRecords" => $d_total_row,
	                    "data" => $data
	                );
	        j($json_data);
	        exit;
		} else {
			$a['p']	= "m_mapel";
		}
		$this->load->view('aaa', $a);
	}
	/* == GURU == */
	public function m_soal() {
		$this->cek_aktif();
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');

		$a['huruf_opsi'] = array("a","b","c","d","e");
		$a['jml_opsi'] = $this->config->item('jml_opsi');

		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		$uri5 = $this->uri->segment(5);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();

		if ($a['sess_level'] == "guru") {
			$a['p_guru'] = obj_to_array($this->db->query("SELECT * FROM m_guru WHERE id = '".$a['sess_konid']."'")->result(), "id,nama");
			$a['p_mapel'] = obj_to_array($this->db->query("SELECT 
											b.id, b.nama
											FROM tr_guru_mapel a
											INNER JOIN m_mapel b ON a.id_mapel = b.id
											WHERE a.id_guru = '".$a['sess_konid']."'")->result(), "id,nama");
		} else {
			$a['p_guru'] = obj_to_array($this->db->query("SELECT * FROM m_guru")->result(), "id,nama");
			$a['p_mapel'] = obj_to_array($this->db->query("SELECT 
											b.id, b.nama
											FROM tr_guru_mapel a
											INNER JOIN m_mapel b ON a.id_mapel = b.id")->result(), "id,nama");
		}

		if ($uri3 == "det") {
			$a = $this->db->query("SELECT * FROM m_soal WHERE id = '$uri4' ORDER BY id DESC")->row();
			j($a);
			exit();
		} else if ($uri3 == "import") {
			$a['p']	= "f_soal_import";
		} else if ($uri3 == "hapus_gambar") {
			$nama_gambar = $this->db->query("SELECT file FROM m_soal WHERE id = '".$uri5."'")->row();
			$this->db->query("UPDATE m_soal SET file = '', tipe_file = '' WHERE id = '".$uri5."'");
			@unlink("./upload/gambar_soal/".$nama_gambar->file);
			redirect('adm/m_soal/pilih_mapel/'.$uri4);
		} else if ($uri3 == "pilih_mapel") {
			if ($a['sess_level'] == "guru") {
				$a['data'] = $this->db->query("SELECT m_soal.*, m_guru.nama AS nama_guru FROM m_soal INNER JOIN m_guru ON m_soal.id_guru = m_guru.id WHERE m_soal.id_guru = '".$a['sess_konid']."' AND m_soal.id_mapel = '$uri4' ORDER BY id DESC")->result();
			} else {
				$a['data'] = $this->db->query("SELECT m_soal.*, m_guru.nama AS nama_guru FROM m_soal INNER JOIN m_guru ON m_soal.id_guru = m_guru.id WHERE m_soal.id_mapel = '$uri4' ORDER BY id DESC")->result();
			}
			//echo $this->db->last_query();
			$a['p']	= "m_soal";
		} else if ($uri3 == "simpan") {
			$p = $this->input->post();
			$pembuat_soal = ($a['sess_level'] == "admin") ? $p['id_guru'] : $a['sess_konid'];
			$pembuat_soal_u = ($a['sess_level'] == "admin") ? ", id_guru = '".$p['id_guru']."'" : "";
			//etok2nya config
			$folder_gb_soal = "./upload/gambar_soal/";
			$folder_gb_opsi = "./upload/gambar_opsi/";

			$buat_folder_gb_soal = !is_dir($folder_gb_soal) ? @mkdir("./upload/gambar_soal/") : false;
			$buat_folder_gb_opsi = !is_dir($folder_gb_opsi) ? @mkdir("./upload/gambar_opsi/") : false;

			$allowed_type 	= array("image/jpeg", "image/png", "image/gif", 
			"audio/mpeg", "audio/mpg", "audio/mpeg3", "audio/mp3", "audio/x-wav", "audio/wave", "audio/wav",
			"video/mp4", "application/octet-stream");

			$gagal 		= array();
			$nama_file 	= array();
			$tipe_file 	= array();

			//get mode
			$__mode = $p['mode'];
			$__id_soal = 0;
			//ambil data post sementara
			$pdata = array(
				"id_guru"=>$p['id_guru'],
				"id_mapel"=>$p['id_mapel'],
				"bobot"=>$p['bobot'],
				"soal"=>$p['soal'],
				"jawaban"=>$p['jawaban'],
			);

			if ($__mode == "edit") {
				$this->db->where("id", $p['id']);
				$this->db->update("m_soal", $pdata);
				$__id_soal = $p['id'];
			} else {
				$this->db->insert("m_soal", $pdata);
				$get_id_akhir = $this->db->query("SELECT MAX(id) maks FROM m_soal LIMIT 1")->row_array();
				$__id_soal = $get_id_akhir['maks'];
			}

			//mulai dari sini id soal diambil dari variabel $__id

			//lakukan perulangan sejumlah file upload yang terdeteksi
			foreach ($_FILES as $k => $v) {
				//var file upload
				//$k = nama field di form
				$file_name 		= $_FILES[$k]['name'];
				$file_type		= $_FILES[$k]['type'];
				$file_tmp		= $_FILES[$k]['tmp_name'];
				$file_error		= $_FILES[$k]['error'];
				$file_size		= $_FILES[$k]['size'];
				//kode ref file upload jika error
				$kode_file_error = array("File berhasil diupload", "Ukuran file terlalu besar", "Ukuran file terlalu besar", "File upload error", "Tidak ada file yang diupload", "File upload error");
				
				//jika file error = 0 / tidak ada, tipe file ada di file yang diperbolehkan, dan nama file != kosong
				//echo $file_error."<br>".$file_type;
				//exit;
				//echo var_dump($file_error == 0 || in_array($file_type, $allowed_type) || $file_name != "");
				//exit;
				if ($file_error != 0) {
					$gagal[$k] = $kode_file_error[$file_error];
					$nama_file[$k]	= "";
					$tipe_file[$k]	= "";
				} else if (!in_array($file_type, $allowed_type)) {
					$gagal[$k] = "Tipe file ini tidak diperbolehkan..";
					$nama_file[$k]	= "";
					$tipe_file[$k]	= "";
				} else if ($file_name == "") {
					$gagal[$k] = "Tidak ada file yang diupload";
					$nama_file[$k]	= "";
					$tipe_file[$k]	= "";					
				} else {
					$ekstensi = explode(".", $file_name);

					$file_name = $k."_".$__id_soal.".".$ekstensi[1];

					if ($k == "gambar_soal") {
						@move_uploaded_file($file_tmp, $folder_gb_soal.$file_name);
					} else {
						@move_uploaded_file($file_tmp, $folder_gb_opsi.$file_name);
					}

					$gagal[$k]	 	= $kode_file_error[$file_error]; //kode kegagalan upload file
					$nama_file[$k]	= $file_name; //ambil nama file
					$tipe_file[$k]	= $file_type; //ambil tipe file
				}
			}


			//ambil data awal
			$get_opsi_awal = $this->db->query("SELECT opsi_a, opsi_b, opsi_c, opsi_d, opsi_e FROM m_soal WHERE id = '".$__id_soal."'")->row_array();

			$data_simpan = array();

			if (!empty($nama_file['gambar_soal'])) {
				$data_simpan = array(
								"file"=>$nama_file['gambar_soal'],
								"tipe_file"=>$tipe_file['gambar_soal'],
								);
			}

			for ($t = 0; $t < $a['jml_opsi']; $t++) {
				$idx 	= "opsi_".$a['huruf_opsi'][$t];
				$idx2 	= "gj".$a['huruf_opsi'][$t];


				//jika file kosong
				$pc_opsi_awal = explode("#####", $get_opsi_awal[$idx]);
				$nama_file_opsi = empty($nama_file[$idx2]) ? $pc_opsi_awal[0] : $nama_file[$idx2];

				$data_simpan[$idx] = $nama_file_opsi."#####".$p[$idx];
			}

			$this->db->where("id", $__id_soal);
			$this->db->update("m_soal", $data_simpan);

			$teks_gagal = "";
			foreach ($gagal as $k => $v) {
				$arr_nama_file_upload = array("gambar_soal"=>"File Soal ", "gja"=>"File opsi A ", "gjb"=>"File opsi B ", "gjc"=>"File opsi C ", "gjd"=>"File opsi D ", "gje"=>"File opsi E ");
				$teks_gagal .= $arr_nama_file_upload[$k].': '.$v.'<br>';
			}
			$this->session->set_flashdata('k', '<div class="alert alert-info">'.$teks_gagal.'</div>');
			
			redirect('adm/m_soal/pilih_mapel/'.$id_mapel);
		} else if ($uri3 == "edit") {
			$a['opsij'] = array(""=>"Jawaban","A"=>"A","B"=>"B","C"=>"C","D"=>"D","E"=>"E");
			
			$id_guru = $this->session->userdata('admin_level') == "guru" ? "WHERE a.id_guru = '".$a['sess_konid']."'" : "";

			$a['p_mapel'] = obj_to_array($this->db->query("SELECT b.id, b.nama FROM tr_guru_mapel a INNER JOIN m_mapel b ON a.id_mapel = b.id $id_guru")->result(),"id,nama");

			if ($uri4 == 0) {
				$a['d'] = array("mode"=>"add","id"=>"0","id_guru"=>$id_guru,"id_mapel"=>"","bobot"=>"1","file"=>"","soal"=>"","opsi_a"=>"#####","opsi_b"=>"#####","opsi_c"=>"#####","opsi_d"=>"#####","opsi_e"=>"#####","jawaban"=>"","tgl_input"=>"");
			} else {
				$a['d'] = $this->db->query("SELECT m_soal.*, 'edit' AS mode FROM m_soal WHERE id = '$uri4'")->row_array();

			}

			$data = array();

			for ($e = 0; $e < $a['jml_opsi']; $e++) {
				$iidata = array();
				$idx = "opsi_".$a['huruf_opsi'][$e];
				$idx2 = $a['huruf_opsi'][$e];

				$pc_opsi_edit = explode("#####", $a['d'][$idx]);
				$iidata['opsi'] = $pc_opsi_edit[1];
				$iidata['gambar'] = $pc_opsi_edit[0];
				$data[$idx2] = $iidata;
			}


			$a['data_pc'] = $data;
			$a['p'] = "f_soal";

		} else if ($uri3 == "hapus") {
			$nama_gambar = $this->db->query("SELECT id_mapel, file, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e FROM m_soal WHERE id = '".$uri4."'")->row();
			$pc_opsi_a = explode("#####", $nama_gambar->opsi_a);
			$pc_opsi_b = explode("#####", $nama_gambar->opsi_b);
			$pc_opsi_c = explode("#####", $nama_gambar->opsi_c);
			$pc_opsi_d = explode("#####", $nama_gambar->opsi_d);
			$pc_opsi_e = explode("#####", $nama_gambar->opsi_e);
			$this->db->query("DELETE FROM m_soal WHERE id = '".$uri4."'");
			@unlink("./upload/gambar_soal/".$nama_gambar->file);
			@unlink("./upload/gambar_soal/".$pc_opsi_a[0]);
			@unlink("./upload/gambar_soal/".$pc_opsi_b[0]);
			@unlink("./upload/gambar_soal/".$pc_opsi_c[0]);
			@unlink("./upload/gambar_soal/".$pc_opsi_d[0]);
			@unlink("./upload/gambar_soal/".$pc_opsi_e[0]);
			
			redirect('adm/m_soal/pilih_mapel/'.$nama_gambar->id_mapel);
		} else if ($uri3 == "cetak") {
			$html = "<link href='".base_url()."___/css/style_print.css' rel='stylesheet' media='' type='text/css'/>";
			if ($a['sess_level'] == "admin") {
				$data = $this->db->query("SELECT * FROM m_soal")->result();
			} else {
				$data = $this->db->query("SELECT * FROM m_soal WHERE id_guru = '".$a['sess_konid']."'")->result();
			}

			$mapel = $this->db->query("SELECT nama FROM m_mapel WHERE id = '".$uri4."'")->row();
			if (!empty($data)) {
				
				$no = 1;
				$jawaban = array("A","B","C","D","E");
				foreach ($data as $d) {
					
		            $arr_tipe_media = array(""=>"none","image/jpeg"=>"gambar","image/png"=>"gambar","image/gif"=>"gambar",
					"audio/mpeg"=>"audio","audio/mpg"=>"audio","audio/mpeg3"=>"audio","audio/mp3"=>"audio","audio/x-wav"=>"audio","audio/wave"=>"audio","audio/wav"=>"audio",
					"video/mp4"=>"video", "application/octet-stream"=>"video");
		            $tipe_media = $arr_tipe_media[$d->tipe_file];
		            $file_ada = file_exists("./upload/gambar_soal/".$d->file) ? "ada" : "tidak_ada";
		            $tampil_media = "";
		            if ($file_ada == "ada" && $tipe_media == "audio") {
		              $tampil_media = '<<< Ada media Audionya >>>';
		            } else if ($file_ada == "ada" && $tipe_media == "video") {
		              $tampil_media = '<<< Ada media Videonya >>>';
		            } else if ($file_ada == "ada" && $tipe_media == "gambar") {
		              $tampil_media = '<p><img src="'.base_url().'upload/gambar_soal/'.$d->file.'" class="thumbnail" style="width: 300px; height: 280px; display: inline; float: left"></p>';
		            } else {
		              $tampil_media = '';
		            }
	                $html .= '<table>
	                <tr><td>'.$no.'.</td><td colspan="2">'.$d->soal.'<br>'.$tampil_media.'</td></tr>';
	                for ($j=0; $j<($this->config->item('jml_opsi'));$j++) {
	                  	$opsi = "opsi_".strtolower($jawaban[$j]);
	                    $pc_pilihan_opsi = explode("#####", $d->$opsi);
	                    $tampil_media_opsi = (file_exists('./upload/gambar_soal/'.$pc_pilihan_opsi[0]) AND $pc_pilihan_opsi[0] != "") ? '<img src="'.base_url().'upload/gambar_soal/'.$pc_pilihan_opsi[0].'" style="width: 100px; height: 100px; margin-right: 20px">' : '';
	                  if ($jawaban[$j] == $d->jawaban) {
	                    $html .= '<tr><td width="2%" style="font-weight: bold">'.$jawaban[$j].'</td><td style="font-weight: bold">'.$tampil_media_opsi.$pc_pilihan_opsi[1].'</td></label></tr>';
	                  } else {
	                    $html .= '<tr><td width="2%">'.$jawaban[$j].'</td><td>'.$tampil_media_opsi.$pc_pilihan_opsi[1].'</td></label></tr>';
	                  }
	                }
	                $html .= '</table></div>';
		            $no++;
				}
				}

				echo $html;
				exit;
			} else if ($uri3 == "data") {
				$start = $this->input->post('start');
		        $length = $this->input->post('length');
		        $draw = $this->input->post('draw');
		        $search = $this->input->post('search');

		        $wh = '';

		        if ($a['sess_level'] == "guru") {
					$wh = "a.id_guru = '".$a['sess_konid']."' AND ";
				} else if ($a['sess_level'] == "admin") {
					$wh = "";
				}


		        $d_total_row = $this->db->query("SELECT a.*
												FROM m_soal a
												INNER JOIN m_guru b ON a.id_guru = b.id
												INNER JOIN m_mapel c ON a.id_mapel = c.id
		                                        WHERE ".$wh." (a.soal LIKE '%".$search['value']."%' 
												OR b.nama LIKE '%".$search['value']."%' 
												OR c.nama LIKE '%".$search['value']."%')")->num_rows();

		        $q_datanya = $this->db->query("SELECT a.*, b.nama nmguru, c.nama nmmapel
												FROM m_soal a
												INNER JOIN m_guru b ON a.id_guru = b.id
												INNER JOIN m_mapel c ON a.id_mapel = c.id
		                                        WHERE ".$wh." (a.soal LIKE '%".$search['value']."%' 
												OR b.nama LIKE '%".$search['value']."%' 
												OR c.nama LIKE '%".$search['value']."%')
		                                        ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();
		        //echo $this->db->last_query();
		    
		        $data = array();
		        $no = ($start+1);

		        foreach ($q_datanya as $d) {
		            $data_ok = array();
		            $data_ok[0] = $no++;
		            $data_ok[1] = substr($d['soal'], 0, 300);
		            $data_ok[2] = $d['nmmapel'].'<br>'.$d['nmguru'];
		            $data_ok[3] = "Jml dipakai : ".($d['jml_benar']+$d['jml_salah'])."<br>Benar: ".$d['jml_benar'].", Salah: ".$d['jml_salah'];
		            $data_ok[4] = '<div class="btn-group">
	                          <a href="'.base_url().'adm/m_soal/edit/'.$d['id'].'" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Edit</a>
	                          <a href="'.base_url().'adm/m_soal/hapus/'.$d['id'].'" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-remove" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Hapus</a>
	                         ';

		            $data[] = $data_ok;
		        }

		        $json_data = array(
		                    "draw" => $draw,
		                    "iTotalRecords" => $d_total_row,
		                    "iTotalDisplayRecords" => $d_total_row,
		                    "data" => $data
		                );
		        j($json_data);
		        exit;
		} else {
			$a['p']	= "m_soal";
		}
		$this->load->view('aaa', $a);
	}
	public function m_ujian() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();
		
		//$a['data'] = $this->db->query("SELECT tr_guru_tes.*, m_mapel.nama AS mapel FROM tr_guru_tes INNER JOIN m_mapel ON tr_guru_tes.id_mapel = m_mapel.id WHERE tr_guru_tes.id_guru = '".$a['sess_konid']."'")->result();

		$a['pola_tes'] = array(""=>"Pengacakan Soal", "acak"=>"Soal Diacak", "set"=>"Soal Diurutkan");

		$a['p_mapel'] = obj_to_array($this->db->query("SELECT * FROM m_mapel WHERE id IN (SELECT id_mapel FROM tr_guru_mapel WHERE id_guru = '".$a['sess_konid']."')")->result(), "id,nama");
		
		if ($uri3 == "det") {
			$are = array();

			$a = $this->db->query("SELECT * FROM tr_guru_tes WHERE id = '$uri4'")->row();
			
			if (!empty($a)) {
				$pc_waktu = explode(" ", $a->tgl_mulai);
				$pc_tgl = explode("-", $pc_waktu[0]);

				$are['id'] = $a->id;
				$are['id_guru'] = $a->id_guru;
				$are['id_mapel'] = $a->id_mapel;
				$are['nama_ujian'] = $a->nama_ujian;
				$are['jumlah_soal'] = $a->jumlah_soal;
				$are['waktu'] = $a->waktu;
				$are['terlambat'] = $a->terlambat;
				$are['jenis'] = $a->jenis;
				$are['detil_jenis'] = $a->detil_jenis;
				$are['tgl_mulai'] = $pc_waktu[0];
				$are['wkt_mulai'] = substr($pc_waktu[1],0,5);
				$are['token'] = $a->token;
			} else {
				$are['id'] = "";
				$are['id_guru'] = "";
				$are['id_mapel'] = "";
				$are['nama_ujian'] = "";
				$are['jumlah_soal'] = "";
				$are['waktu'] = "";
				$are['terlambat'] = "";
				$are['jenis'] = "";
				$are['detil_jenis'] = "";
				$are['tgl_mulai'] = "";
				$are['wkt_mulai'] = "";
				$are['token'] = "";
			}

			j($are);
			exit();
		} else if ($uri3 == "simpan") {
			$ket 	= "";

			if ($p->id != 0) {
				$this->db->query("UPDATE tr_guru_tes SET id_mapel = '".bersih($p,"mapel")."', 
								nama_ujian = '".bersih($p,"nama_ujian")."', jumlah_soal = '".bersih($p,"jumlah_soal")."', 
								waktu = '".bersih($p,"waktu")."', terlambat = '".bersih($p,"terlambat")."', 
								tgl_mulai = '".bersih($p,"tgl_mulai")." ".bersih($p,"wkt_mulai")."', jenis = '".bersih($p,"acak")."'
								WHERE id = '".bersih($p,"id")."'");
				$ket = "edit";
			} else {
				$ket = "tambah";
				$token = strtoupper(random_string('alpha', 5));

				$this->db->query("INSERT INTO tr_guru_tes VALUES (null, '".$a['sess_konid']."', '".bersih($p,"mapel")."',
								'".bersih($p,"nama_ujian")."', '".bersih($p,"jumlah_soal")."', '".bersih($p,"waktu")."', '".bersih($p,"acak")."', 
								'', '".bersih($p,"tgl_mulai")." ".bersih($p,"wkt_mulai")."', '".
								bersih($p,"terlambat")."', '$token')");
			}
			
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= $ket." sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "hapus") {
			$this->db->query("DELETE FROM tr_guru_tes WHERE id = '".$uri4."'");
			$ret_arr['status'] 	= "ok";
			$ret_arr['caption']	= "hapus sukses";
			j($ret_arr);
			exit();
		} else if ($uri3 == "jumlah_soal") {
			$ambil_data = $this->db->query("SELECT id FROM m_soal WHERE id_mapel = '$uri4' AND id_guru = '".$a['sess_konid']."'")->num_rows();
			$ret_arr['jumlah'] = $ambil_data;
			j($ret_arr);
			exit();			
		} else if ($uri3 == "data") {
				$start = $this->input->post('start');
		        $length = $this->input->post('length');
		        $draw = $this->input->post('draw');
		        $search = $this->input->post('search');

		        $d_total_row = $this->db->query("SELECT a.id
		        	FROM tr_guru_tes a
		        	INNER JOIN m_mapel b ON a.id_mapel = b.id 
		        	WHERE a.id_guru = '".$a['sess_konid']."' 
                    AND (a.nama_ujian LIKE '%".$search['value']."%' 
					OR b.nama LIKE '%".$search['value']."%')")->num_rows();
		    	
		    	//echo $this->db->last_query();

		        $q_datanya = $this->db->query("SELECT a.*, b.nama AS mapel
												FROM tr_guru_tes a
									        	INNER JOIN m_mapel b ON a.id_mapel = b.id 
									        	WHERE a.id_guru = '".$a['sess_konid']."'
							                    AND (a.nama_ujian LIKE '%".$search['value']."%'
												OR b.nama LIKE '%".$search['value']."%') 
		                                        ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();
		        $data = array();
		        $no = ($start+1);

		        foreach ($q_datanya as $d) {
		        	$jenis_soal = $d['jenis'] == "acak" ? "Soal diacak" : "Soal urut";
                
		            $data_ok = array();
		            $data_ok[0] = $no++;
		            $data_ok[1] = $d['nama_ujian']."<br>Token : <b>".$d['token']."</b> &nbsp;&nbsp; <a href='#' onclick='return refresh_token(".$d['id'].")' title='Perbarui Token'><i class='fa fa-refresh'></i></a>";
		            $data_ok[2] = $d['mapel'];
		            $data_ok[3] = $d['jumlah_soal'];
		            $data_ok[4] = tjs($d['tgl_mulai'],"s")."<br>(".$d['waktu']." menit)";
		            $data_ok[5] = $jenis_soal;
		            $data_ok[6] = '
		            	<div class="btn-group">
                          <a href="#" onclick="return m_ujian_e('.$d['id'].');" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-pencil" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Edit</a>
                          <a href="#" onclick="return m_ujian_h('.$d['id'].');" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-remove" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Hapus</a>
                        </div>
	                         ';

		            $data[] = $data_ok;
		        }

		        $json_data = array(
		                    "draw" => $draw,
		                    "iTotalRecords" => $d_total_row,
		                    "iTotalDisplayRecords" => $d_total_row,
		                    "data" => $data
		                );
		        j($json_data);
		        exit;
		} else if ($uri3 == "refresh_token") {
			$token = strtoupper(random_string('alpha', 5));

			$this->db->query("UPDATE tr_guru_tes SET token = '$token' WHERE id = '$uri4'");

			$ret_arr['status'] = "ok";
			j($ret_arr);
			exit();
		} else {
			$a['p']	= "m_guru_tes";
		}
		$this->load->view('aaa', $a);
	}
	public function h_ujian() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		$uri5 = $this->uri->segment(5);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();

		$wh_1 = $a['sess_level'] == "admin" ? "" : " AND a.id_guru = '".$a['sess_konid']."'";
		//$a['data'] = $this->db->query($wh_1)->result();
		

		$a['p_mapel'] = obj_to_array($this->db->query("SELECT * FROM m_mapel")->result(), "id,nama");
		
		if ($uri3 == "det") {
			$a['detil_tes'] = $this->db->query("SELECT m_mapel.nama AS namaMapel, m_guru.nama AS nama_guru, 
												tr_guru_tes.* 
												FROM tr_guru_tes 
												INNER JOIN m_mapel ON tr_guru_tes.id_mapel = m_mapel.id
												INNER JOIN m_guru ON tr_guru_tes.id_guru = m_guru.id
												WHERE tr_guru_tes.id = '$uri4'")->row();
			$a['statistik'] = $this->db->query("SELECT MAX(nilai) AS max_, MIN(nilai) AS min_, AVG(nilai) AS avg_ 
											FROM tr_ikut_ujian
											WHERE tr_ikut_ujian.id_tes = '$uri4'")->row();

			//$a['hasil'] = $this->db->query("")->result();
			$a['p'] = "m_guru_tes_hasil_detil";
			//echo $this->db->last_query();
		} else if ($uri3 == "data_det") {
			$start = $this->input->post('start');
	        $length = $this->input->post('length');
	        $draw = $this->input->post('draw');
	        $search = $this->input->post('search');

	        $d_total_row = $this->db->query("
	        	SELECT a.id
				FROM tr_ikut_ujian a
				INNER JOIN m_siswa b ON a.id_user = b.id
				WHERE a.id_tes = '$uri4' 
				AND b.nama LIKE '%".$search['value']."%'")->num_rows();

	        $q_datanya = $this->db->query("
	        	SELECT a.id, b.nama, a.nilai, a.jml_benar, a.nilai_bobot
				FROM tr_ikut_ujian a
				INNER JOIN m_siswa b ON a.id_user = b.id
				WHERE a.id_tes = '$uri4' 
				AND b.nama LIKE '%".$search['value']."%' ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();

	        $data = array();
	        $no = ($start+1);


	        foreach ($q_datanya as $d) {
	            $data_ok = array();
	            $data_ok[0] = $no++;
	            $data_ok[1] = $d['nama'];
	            $data_ok[2] = $d['jml_benar'];
	            $data_ok[3] = $d['nilai'];
	            $data_ok[4] = $d['nilai_bobot'];
	            $data_ok[5] = '<a href="'.base_url().'adm/h_ujian/batalkan_ujian/'.$d['id'].'/'.$this->uri->segment(4).'" class="btn btn-danger btn-xs" onclick="return confirm(\'Anda yakin...?\');"><i class="glyphicon glyphicon-remove" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Batalkan Ujian</a>';

	            $data[] = $data_ok;
	        }

	        $json_data = array(
	                    "draw" => $draw,
	                    "iTotalRecords" => $d_total_row,
	                    "iTotalDisplayRecords" => $d_total_row,
	                    "data" => $data
	                );
	        j($json_data);
	        exit;
		} else if ($uri3 == "batalkan_ujian") {
			$this->db->query("DELETE FROM tr_ikut_ujian WHERE id = '$uri4'");
			redirect('adm/h_ujian/det/'.$uri5);
		} else if ($uri3 == "data") {
			$start = $this->input->post('start');
	        $length = $this->input->post('length');
	        $draw = $this->input->post('draw');
	        $search = $this->input->post('search');

	        $d_total_row = $this->db->query("SELECT a.id FROM tr_guru_tes a
	        	INNER JOIN m_mapel b ON a.id_mapel = b.id 
	        	INNER JOIN m_guru c ON a.id_guru = c.id
	            WHERE (a.nama_ujian LIKE '%".$search['value']."%' OR b.nama LIKE '%".$search['value']."%' OR c.nama LIKE '%".$search['value']."%') ".$wh_1."")->num_rows();
	    	//echo $this->db->last_query();

	        $q_datanya = $this->db->query("SELECT a.*, b.nama AS mapel, c.nama AS nama_guru FROM tr_guru_tes a
	        	INNER JOIN m_mapel b ON a.id_mapel = b.id 
	        	INNER JOIN m_guru c ON a.id_guru = c.id
	            WHERE (a.nama_ujian LIKE '%".$search['value']."%' OR b.nama LIKE '%".$search['value']."%' OR c.nama LIKE '%".$search['value']."%') ".$wh_1." ORDER BY a.id DESC LIMIT ".$start.", ".$length."")->result_array();

	        $data = array();
	        $no = ($start+1);


	        foreach ($q_datanya as $d) {
	            $data_ok = array();
	            $data_ok[0] = $no++;
	            $data_ok[1] = $d['nama_ujian'];
	            $data_ok[2] = $d['nama_guru'];
	            $data_ok[3] = $d['mapel'];
	            $data_ok[4] = $d['jumlah_soal'];
	            $data_ok[5] = $d['waktu']." menit";
	            $data_ok[6] = '<a href="'.base_url().'adm/h_ujian/det/'.$d['id'].'" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-search" style="margin-left: 0px; color: #fff"></i> &nbsp;&nbsp;Lihat Hasil</a>
                         ';

	            $data[] = $data_ok;
	        }

	        $json_data = array(
	                    "draw" => $draw,
	                    "iTotalRecords" => $d_total_row,
	                    "iTotalDisplayRecords" => $d_total_row,
	                    "data" => $data
	                );
	        j($json_data);
	        exit;
		} else {
			$a['p']	= "m_guru_tes_hasil";
		}


		$this->load->view('aaa', $a);
	}
	public function hasil_ujian_cetak() {
		$this->cek_aktif();
		
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		$a['detil_tes'] = $this->db->query("SELECT m_mapel.nama AS namaMapel, m_guru.nama AS nama_guru, 
												tr_guru_tes.* 
												FROM tr_guru_tes 
												INNER JOIN m_mapel ON tr_guru_tes.id_mapel = m_mapel.id
												INNER JOIN m_guru ON tr_guru_tes.id_guru = m_guru.id
												WHERE tr_guru_tes.id = '$uri3'")->row();
		
		$a['statistik'] = $this->db->query("SELECT MAX(nilai) AS max_, MIN(nilai) AS min_, AVG(nilai) AS avg_ 
										FROM tr_ikut_ujian
										WHERE tr_ikut_ujian.id_tes = '$uri3'")->row();
		$a['hasil'] = $this->db->query("SELECT m_siswa.nama, tr_ikut_ujian.nilai, tr_ikut_ujian.jml_benar, tr_ikut_ujian.nilai_bobot
										FROM tr_ikut_ujian
										INNER JOIN m_siswa ON tr_ikut_ujian.id_user = m_siswa.id
										WHERE tr_ikut_ujian.id_tes = '$uri3'")->result();
		$this->load->view("m_guru_tes_hasil_detil_cetak", $a);
	}
	/* == SISWA == */
	public function ikuti_ujian() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		//return as json
		$jeson = array();
		//$a['sess_konid']
		$a['data'] = $this->db->query("SELECT 
									a.id, a.nama_ujian, a.jumlah_soal, a.waktu,
									b.nama nmmapel,
									c.nama nmguru,
									IF((d.status='Y' AND NOW() BETWEEN d.tgl_mulai AND d.tgl_selesai),'Sedang Tes',
									IF(d.status='Y' AND NOW() NOT BETWEEN d.tgl_mulai AND d.tgl_selesai,'Waktu Habis',
									IF(d.status='N','Selesai','Belum Ikut'))) status 
									FROM tr_guru_tes a
									INNER JOIN m_mapel b ON a.id_mapel = b.id
									INNER JOIN m_guru c ON a.id_guru = c.id
									LEFT JOIN tr_ikut_ujian d ON CONCAT('".$a['sess_konid']."',a.id) = CONCAT(d.id_user,d.id_tes)
									ORDER BY a.id ASC")->result();
		//echo $this->db->last_query();
		$a['p']	= "m_list_ujian_siswa";
		$this->load->view('aaa', $a);
	}
	public function ikut_ujian() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		$a['detil_user'] = $this->db->query("SELECT * FROM m_siswa WHERE id = '".$a['sess_konid']."'")->row();
		if ($uri3 == "simpan_satu") {
			$p			= json_decode(file_get_contents('php://input'));
			
			$update_ 	= "";
			for ($i = 1; $i < $p->jml_soal; $i++) {
				$_tjawab 	= "opsi_".$i;
				$_tidsoal 	= "id_soal_".$i;
				$jawaban_ 	= empty($p->$_tjawab) ? "" : $p->$_tjawab;
				$update_	.= "".$p->$_tidsoal.":".$jawaban_.",";
			}
			$update_		= substr($update_, 0, -1);
			$this->db->query("UPDATE tr_ikut_ujian SET list_jawaban = '".$update_."' WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."'");
			//echo $this->db->last_query();

			$q_ret_urn 	= $this->db->query("SELECT list_jawaban FROM tr_ikut_ujian WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."'");
			
			$d_ret_urn 	= $q_ret_urn->row_array();
			$ret_urn 	= explode(",", $d_ret_urn['list_jawaban']);
			$hasil 		= array();
			foreach ($ret_urn as $key => $value) {
				$pc_ret_urn = explode(":", $value);
				$idx 		= $pc_ret_urn['0'];
				$val 		= $pc_ret_urn['1'];
				$hasil[]= $val;
			}

			$d['data'] = $hasil;
			$d['status'] = "ok";

			j($d);
			exit;		

		} else if ($uri3 == "simpan_akhir") {
			$p			= json_decode(file_get_contents('php://input'));
			
			$jumlah_soal = $p->jml_soal;
			$jumlah_benar = 0;
			//$jumlah_bobot = 0;
			$update_ = "";
			//nilai bobot 
			$array_bobot 	= array();
			$array_nilai	= array();
			for ($i = 1; $i < $p->jml_soal; $i++) {
				$_tjawab 	= "opsi_".$i;
				$_tidsoal 	= "id_soal_".$i;
				$jawaban_ 	= empty($p->$_tjawab) ? "" : $p->$_tjawab;
				$cek_jwb 	= $this->db->query("SELECT bobot, jawaban FROM m_soal WHERE id = '".$p->$_tidsoal."'")->row();
				//untuknilai bobot
				$bobotnya 	= $cek_jwb->bobot;
				$array_bobot[$bobotnya] = empty($array_bobot[$bobotnya]) ? 1 : $array_bobot[$bobotnya] + 1;
				
				$q_update_jwb = "";
				if ($cek_jwb->jawaban == $jawaban_) {
					//jika jawaban benar
					$jumlah_benar++;
					$array_nilai[$bobotnya] = empty($array_nilai[$bobotnya]) ? 1 : $array_nilai[$bobotnya] + 1;
					$q_update_jwb = "UPDATE m_soal SET jml_benar = jml_benar + 1 WHERE id = '".$p->$_tidsoal."'";
				} else {
					//jika jawaban salah
					$array_nilai[$bobotnya] = empty($array_nilai[$bobotnya]) ? 0 : $array_nilai[$bobotnya] + 0;
					$q_update_jwb = "UPDATE m_soal SET jml_salah = jml_salah + 1 WHERE id = '".$p->$_tidsoal."'";
				}

				$this->db->query($q_update_jwb);

				$update_	.= "".$p->$_tidsoal.":".$jawaban_.",";
			}
			//perhitungan nilai bobot
			ksort($array_bobot);
			ksort($array_nilai);
			$nilai_bobot_benar = 0;
			$nilai_bobot_total = 0;
			foreach ($array_bobot as $key => $value) {
				$nilai_bobot_benar = $nilai_bobot_benar + ($key * $array_nilai[$key]);
				$nilai_bobot_total = $nilai_bobot_total + ($key * $array_bobot[$key]);
			}
			$update_		= substr($update_, 0, -1);
			$nilai = ($jumlah_benar/($jumlah_soal-1)) * 100;
			$nilai_bobot = ($nilai_bobot_benar/$nilai_bobot_total)*100;
			
			/*
			echo var_dump($array_bobot);
			echo var_dump($array_nilai);
			echo "Benar bobot : ".$nilai_bobot_benar."<br>";
			echo "Jml bobot : ".$nilai_bobot_total."<br>";
			echo "Nilai bobot : ".$nilai_bobot."<br>";
			//akhir perhitungan nilai bobot
			exit;
			*/
			$this->db->query("UPDATE tr_ikut_ujian SET jml_benar = ".$jumlah_benar.", nilai_bobot = ".$nilai_bobot.", nilai = '".$nilai."', list_jawaban = '".$update_."', status = 'N' WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."'");
			$a['status'] = "ok";
			j($a);
			exit;		
		} else if ($uri3 == "token") {
			$a['du'] = $this->db->query("SELECT a.id, a.tgl_mulai, a.terlambat, 
										a.token, a.nama_ujian, a.jumlah_soal, a.waktu,
										b.nama nmguru, c.nama nmmapel FROM tr_guru_tes a 
										INNER JOIN m_guru b ON a.id_guru = b.id
										INNER JOIN m_mapel c ON a.id_mapel = c.id 
										WHERE a.id = '$uri4'")->row_array();
			$a['dp'] = $this->db->query("SELEcT * FROM m_siswa WHERE id = '".$a['sess_konid']."'")->row_array();

			if (!empty($a['du']) || !empty($a['dp'])) {
				$tgl_selesai = $a['du']['tgl_mulai'];
			    $tgl_selesai = strtotime($tgl_selesai);
			    $tgl_baru = date('F j, Y H:i:s', $tgl_selesai);

			    $tgl_terlambat = strtotime("+".$a['du']['terlambat']." minutes", $tgl_selesai);	
				$tgl_terlambat_baru = date('F j, Y H:i:s', $tgl_terlambat);

				$a['tgl_mulai'] = $tgl_baru;
				$a['terlambat'] = $tgl_terlambat_baru;

				$a['p']	= "m_token";
				$this->load->view('aaa', $a);
			} else {
				redirect('adm/ikuti_ujian');
			}
		} else {
			$cek_sdh_selesai= $this->db->query("SELECT id FROM tr_ikut_ujian WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."' AND status = 'N'")->num_rows();
			
			//sekalian validasi waktu sudah berlalu...
			if ($cek_sdh_selesai < 1) {
				//ini jika ujian belum tercatat, belum ikut
				//ambil detil soal
				$cek_detil_tes = $this->db->query("SELECT * FROM tr_guru_tes WHERE id = '$uri4'")->row();
				$q_cek_sdh_ujian= $this->db->query("SELECT id FROM tr_ikut_ujian WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."'");
				$d_cek_sdh_ujian= $q_cek_sdh_ujian->row();
				$cek_sdh_ujian	= $q_cek_sdh_ujian->num_rows();
				$acakan = $cek_detil_tes->jenis == "acak" ? "ORDER BY RAND()" : "ORDER BY id ASC";

				if ($cek_sdh_ujian < 1)	{		
					$soal_urut_ok = array();
					$q_soal			= $this->db->query("SELECT id, file, tipe_file, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, '' AS jawaban FROM m_soal WHERE id_mapel = '".$cek_detil_tes->id_mapel."' AND id_guru = '".$cek_detil_tes->id_guru."' ".$acakan." LIMIT ".$cek_detil_tes->jumlah_soal)->result();
					$i = 0;
					foreach ($q_soal as $s) {
						$soal_per = new stdClass();
						$soal_per->id = $s->id;
						$soal_per->soal = $s->soal;
						$soal_per->file = $s->file;
						$soal_per->tipe_file = $s->tipe_file;
						$soal_per->opsi_a = $s->opsi_a;
						$soal_per->opsi_b = $s->opsi_b;
						$soal_per->opsi_c = $s->opsi_c;
						$soal_per->opsi_d = $s->opsi_d;
						$soal_per->opsi_e = $s->opsi_e;
						$soal_per->jawaban = $s->jawaban;
						$soal_urut_ok[$i] = $soal_per;
						$i++;
					}
					$soal_urut_ok = $soal_urut_ok;
					$list_id_soal	= "";
					$list_jw_soal 	= "";
					if (!empty($q_soal)) {
						foreach ($q_soal as $d) {
							$list_id_soal .= $d->id.",";
							$list_jw_soal .= $d->id.":,";
						}
					}
					$list_id_soal = substr($list_id_soal, 0, -1);
					$list_jw_soal = substr($list_jw_soal, 0, -1);
					$waktu_selesai = tambah_jam_sql($cek_detil_tes->waktu);
					$time_mulai		= date('Y-m-d H:i:s');
					$this->db->query("INSERT INTO tr_ikut_ujian VALUES (null, '$uri4', '".$a['sess_konid']."', '$list_id_soal', '$list_jw_soal', 0, 0, 0, '$time_mulai', ADDTIME('$time_mulai', '$waktu_selesai'), 'Y')");
					
					$detil_tes = $this->db->query("SELECT * FROM tr_ikut_ujian WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."'")->row();

					$soal_urut_ok= $soal_urut_ok;
				} else {
					$q_ambil_soal 	= $this->db->query("SELECT * FROM tr_ikut_ujian WHERE id_tes = '$uri4' AND id_user = '".$a['sess_konid']."'")->row();

					$urut_soal 		= explode(",", $q_ambil_soal->list_jawaban);
					$soal_urut_ok	= array();
					for ($i = 0; $i < sizeof($urut_soal); $i++) {
						$pc_urut_soal = explode(":",$urut_soal[$i]);
						$pc_urut_soal1 = empty($pc_urut_soal[1]) ? "''" : "'".$pc_urut_soal[1]."'";
						$ambil_soal = $this->db->query("SELECT *, $pc_urut_soal1 AS jawaban FROM m_soal WHERE id = '".$pc_urut_soal[0]."'")->row();
						$soal_urut_ok[] = $ambil_soal; 
					}
					
					$detil_tes = $q_ambil_soal;

					$soal_urut_ok = $soal_urut_ok;
				}


				$pc_list_jawaban = explode(",", $detil_tes->list_jawaban);

				$arr_jawab = array();
				foreach ($pc_list_jawaban as $v) {
				  $pc_v = explode(":", $v);
				  $idx = $pc_v[0];
				  $val = $pc_v[1];

				  $arr_jawab[$idx] = $val;
				}

				$html = '';
				$no = 1;
				if (!empty($soal_urut_ok)) {
				    foreach ($soal_urut_ok as $d) { 
				        $tampil_media = tampil_media("./upload/gambar_soal/".$d->file, 'auto','auto');

				        $html .= '<input type="hidden" name="id_soal_'.$no.'" value="'.$d->id.'">';
				        $html .= '<div class="step" id="widget_'.$no.'">';

				        $html .= '<p>'.$d->soal.'</p><p>'.$tampil_media.'</p><div class="funkyradio">';

				        for ($j = 0; $j < $this->config->item('jml_opsi'); $j++) {
				            $opsi = "opsi_".$this->opsi[$j];

				            $checked = $arr_jawab[$d->id] == strtoupper($this->opsi[$j]) ? "checked" : "";

				            $pc_pilihan_opsi = explode("#####", $d->$opsi);

				            $tampil_media_opsi = (is_file('./upload/gambar_soal/'.$pc_pilihan_opsi[0]) || $pc_pilihan_opsi[0] != "") ? tampil_media('./upload/gambar_opsi/'.$pc_pilihan_opsi[0],'auto','auto') : '';

				            $html .= '<div class="funkyradio-success">
				                <input type="radio" id="opsi_'.strtoupper($this->opsi[$j]).'_'.$d->id.'" name="opsi_'.$no.'" value="'.strtoupper($this->opsi[$j]).'" '.$checked.'> <label for="opsi_'.strtoupper($this->opsi[$j]).'_'.$d->id.'"><div class="huruf_opsi">'.$this->opsi[$j].'</div> <p>'.$pc_pilihan_opsi[1].'</p><p>'.$tampil_media_opsi.'</p></label></div>';
				        }
				        $html .= '</div></div>';
				        $no++;
				    }
				}

				$a['jam_mulai'] = $detil_tes->tgl_mulai;
				$a['jam_selesai'] = $detil_tes->tgl_selesai;
				$a['id_tes'] = $cek_detil_tes->id;
				$a['no'] = $no;
				$a['html'] = $html;

				$this->load->view('v_ujian', $a);
			} else {
				redirect('adm/sudah_selesai_ujian/'.$uri4);
			}
		}

	}
	public function jvs() {
		$this->cek_aktif();
		
		$data_soal 		= $this->db->query("SELECT id, gambar, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e FROM m_soal ORDER BY RAND()")->result();
		
		j($data_soal);
		exit;
	}
	public function rubah_password() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_admin_id'] = $this->session->userdata('admin_id');
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		//var post from json
		$p = json_decode(file_get_contents('php://input'));
		$ret = array();
		if ($uri3 == "simpan") {
			$p1_md5 = md5($p->p1);
			$p2_md5 = md5($p->p2);
			$p3_md5 = md5($p->p3);
			$cek_pass_lama = $this->db->query("SELECT password FROM m_admin WHERE id = '".$a['sess_admin_id']."'")->row();
			if ($cek_pass_lama->password != $p1_md5) {
				$ret['status'] = "error";
				$ret['msg'] = "Password lama tidak sama...";
			} else if ($p2_md5 != $p3_md5) {
				$ret['status'] = "error";
				$ret['msg'] = "Password baru konfirmasinya tidak sama...";
			} else if (strlen($p->p2) < 6) {
				$ret['status'] = "error";
				$ret['msg'] = "Password baru minimal terdiri dari 6 huruf..";
 			} else {
				$this->db->query("UPDATE m_admin SET password = '".$p3_md5."' WHERE id = '".$a['sess_admin_id']."'");
				$ret['status'] = "ok";
				$ret['msg'] = "Password berhasil diubah...";
			}
			j($ret);
			exit;
		} else {
			$data = $this->db->query("SELECT id, kon_id, level, username FROM m_admin WHERE id = '".$a['sess_admin_id']."'")->row();
			j($data);
			exit;
		}
	}
	public function sudah_selesai_ujian() {
		$this->cek_aktif();
		
		//var def session
		$a['sess_level'] = $this->session->userdata('admin_level');
		$a['sess_user'] = $this->session->userdata('admin_user');
		$a['sess_konid'] = $this->session->userdata('admin_konid');
		//var def uri segment
		$uri2 = $this->uri->segment(2);
		$uri3 = $this->uri->segment(3);
		$uri4 = $this->uri->segment(4);
		
		$q_nilai = $this->db->query("SELECT nilai, tgl_selesai FROM tr_ikut_ujian WHERE id_tes = $uri3 AND id_user = '".$a['sess_konid']."' AND status = 'N'")->row();
		if (empty($q_nilai)) {
			redirect('adm/ikut_ujian/_/'.$uri3);
		} else {
			$a['p'] = "v_selesai_ujian";
			$a['data'] = "<div class='alert alert-danger'>Anda telah selesai mengikuti ujian ini pada : <strong style='font-size: 16px'>".tjs($q_nilai->tgl_selesai, "l")."</strong>, dan mendapatkan nilai : <strong style='font-size: 16px'>".$q_nilai->nilai."</strong></div>";
		}
		$this->load->view('aaa', $a);
	}
	/* Login Logout */
	public function login() {
		$this->load->view('aaa_login');
	}
	
	public function act_login() {
		
		$username	= $this->input->post('username');
		$password	= $this->input->post('password');
		
		$password2	= md5($password);
		
		$q_data		= $this->db->query("SELECT * FROM m_admin WHERE username = '".$username."' AND password = '$password2'");
		$j_data		= $q_data->num_rows();
		$a_data		= $q_data->row();
		
		$_log		= array();
		if ($j_data === 1) {
			$sess_nama_user = "";
			if ($a_data->level == "siswa") {
				$det_user = $this->db->query("SELECT nama FROM m_siswa WHERE id = '".$a_data->kon_id."'")->row();
				if (!empty($det_user)) {
					$sess_nama_user = $det_user->nama;
				}
			} else if ($a_data->level == "guru") {
				$det_user = $this->db->query("SELECT nama FROM m_guru WHERE id = '".$a_data->kon_id."'")->row();
				if (!empty($det_user)) {
					$sess_nama_user = $det_user->nama;
				}
			} else {
				$sess_nama_user = "Administrator Pusat";
			}
			$data = array(
                    'admin_id' => $a_data->id,
                    'admin_user' => $a_data->username,
                    'admin_level' => $a_data->level,
                    'admin_konid' => $a_data->kon_id,
                    'admin_nama' => $sess_nama_user,
					'admin_valid' => true
                    );
            $this->session->set_userdata($data);
			$_log['log']['status']			= "1";
			$_log['log']['keterangan']		= "Login berhasil";
			$_log['log']['detil_admin']		= $this->session->userdata;
		} else {
			$_log['log']['status']			= "0";
			$_log['log']['keterangan']		= "Maaf, username dan password tidak ditemukan";
			$_log['log']['detil_admin']		= null;
		}
		
		j($_log);
	}
	
	public function logout() {
		$data = array(
                    'admin_id' 		=> "",
                    'admin_user' 	=> "",
                    'admin_level' 	=> "",
                    'admin_konid' 	=> "",
                    'admin_nama' 	=> "",
					'admin_valid' 	=> false
                    );
        $this->session->set_userdata($data);
		redirect('adm');
	}
	//fungsi tambahan
	public function get_akhir($tabel, $field, $kode_awal, $pad) {
		$get_akhir	= $this->db->query("SELECT MAX($field) AS max FROM $tabel LIMIT 1")->row();
		$data		= (intval($get_akhir->max)) + 1;
		$last		= $kode_awal.str_pad($data, $pad, '0', STR_PAD_LEFT);
	
		return $last;
	}
	
	
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
