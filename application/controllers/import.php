<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set("Asia/Jakarta");
class Import extends CI_Controller {
	function __construct() {
	    parent::__construct();
	    $this->db->query("SET time_zone='+7:00'");

        $this->kolom_xl = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	}
	
	public function cek_aktif() {
		if ($this->session->userdata('admin_valid') == false && $this->session->userdata('admin_id') == "") {
			redirect('adm/login');
		} 
	}

	public function siswa() {
        $idx_baris_mulai = 3;
        $idx_baris_selesai = 100;

        $target_file = './upload/temp/';
        $buat_folder_temp = !is_dir($target_file) ? @mkdir("./upload/temp/") : false;
        
        move_uploaded_file($_FILES["import_excel"]["tmp_name"], $target_file.$_FILES['import_excel']['name']);

        $file   = explode('.',$_FILES['import_excel']['name']);
        $length = count($file);

        if($file[$length -1] == 'xlsx' || $file[$length -1] == 'xls') {

            $tmp    = './upload/temp/'.$_FILES['import_excel']['name'];
            //Baca dari tmp folder jadi file ga perlu jadi sampah di server :-p
            
            $this->load->library('excel');//Load library excelnya
            $read   = PHPExcel_IOFactory::createReaderForFile($tmp);
            $read->setReadDataOnly(true);
            $excel  = $read->load($tmp);
    
            $_sheet = $excel->setActiveSheetIndexByName('data');
            
            $data = array();
            for ($j = $idx_baris_mulai; $j <= $idx_baris_selesai; $j++) {
                $nim = $_sheet->getCell("A".$j)->getCalculatedValue();
                $nama = $_sheet->getCell("B".$j)->getCalculatedValue();
                $kelas = $_sheet->getCell("C".$j)->getCalculatedValue();

                if ($nim != "" || $nama != "") {
                    $data[] = "('".$nim."', '".$nama."', '".$kelas."')"; 
                }
            }

            $strq = "INSERT INTO m_siswa (nim, nama, jurusan) VALUES ";
           
            $strq .= implode(",", $data).";";
            
            $this->db->query($strq);
        } else {
            exit('Bukan File Excel...');//pesan error tipe file tidak tepat
        }
        redirect('adm/m_siswa');
	}

	public function guru() {
        $idx_baris_mulai = 3;
        $idx_baris_selesai = 100;

        $target_file = './upload/temp/';
        $buat_folder_temp = !is_dir($target_file) ? @mkdir("./upload/temp/") : false;

        move_uploaded_file($_FILES["import_excel"]["tmp_name"], $target_file.$_FILES['import_excel']['name']);

        $file   = explode('.',$_FILES['import_excel']['name']);
        $length = count($file);

        if($file[$length -1] == 'xlsx' || $file[$length -1] == 'xls') {

            $tmp    = './upload/temp/'.$_FILES['import_excel']['name'];
            //Baca dari tmp folder jadi file ga perlu jadi sampah di server :-p
            
            $this->load->library('excel');//Load library excelnya
            $read   = PHPExcel_IOFactory::createReaderForFile($tmp);
            $read->setReadDataOnly(true);
            $excel  = $read->load($tmp);
    
            $_sheet = $excel->setActiveSheetIndexByName('data');
            
            $data = array();
            for ($j = $idx_baris_mulai; $j <= $idx_baris_selesai; $j++) {
                $nip = $_sheet->getCell("A".$j)->getCalculatedValue();
                $nama = $_sheet->getCell("B".$j)->getCalculatedValue();

                if ($nip != "" || $nama != "") {
                    $data[] = "('".$nip."', '".$nama."')"; 
                }
            }

            $strq = "INSERT INTO m_guru (nip, nama) VALUES ";
           
            $strq .= implode(",", $data).";";
            
            $this->db->query($strq);
        } else {
            exit('Bukan File Excel...');//pesan error tipe file tidak tepat
        }
        redirect('adm/m_guru');
	}

    public function soal() {
        $p = $this->input->post();

        $idx_baris_mulai = 3;
        $idx_baris_selesai = 106;

        $target_file = './upload/temp/';
        $buat_folder_temp = !is_dir($target_file) ? @mkdir("./upload/temp/") : false;
        
        move_uploaded_file($_FILES["import_excel"]["tmp_name"], $target_file.$_FILES['import_excel']['name']);

        $file   = explode('.',$_FILES['import_excel']['name']);
        $length = count($file);

        if($file[$length -1] == 'xlsx' || $file[$length -1] == 'xls') {

            $tmp    = './upload/temp/'.$_FILES['import_excel']['name'];
            //Baca dari tmp folder jadi file ga perlu jadi sampah di server :-p
            
            $this->load->library('excel');//Load library excelnya
            $read   = PHPExcel_IOFactory::createReaderForFile($tmp);
            $read->setReadDataOnly(true);
            $excel  = $read->load($tmp);
    
            $_sheet = $excel->setActiveSheetIndexByName('data');
            
            $data = array();
            for ($j = $idx_baris_mulai; $j <= $idx_baris_selesai; $j++) {
                $bobot = $_sheet->getCell("A".$j)->getCalculatedValue();
                $soal = $_sheet->getCell("B".$j)->getCalculatedValue();
                $opsi_a = $_sheet->getCell("C".$j)->getCalculatedValue();
                $opsi_b = $_sheet->getCell("D".$j)->getCalculatedValue();
                $opsi_c = $_sheet->getCell("E".$j)->getCalculatedValue();
                $opsi_d = $_sheet->getCell("F".$j)->getCalculatedValue();
                $opsi_e = $_sheet->getCell("G".$j)->getCalculatedValue();
                $kunci = $_sheet->getCell("H".$j)->getCalculatedValue();

                if ($soal != "") {
                    $data[] = "('".$p['id_guru']."', '".$p['id_mapel']."', '".$bobot."', '".$soal."', '#####".$opsi_a."', '#####".$opsi_b."', '#####".$opsi_c."', '#####".$opsi_d."', '#####".$opsi_e."', '".$kunci."', NOW(), 0, 0)"; 
                }
            }

            $strq = "INSERT INTO m_soal (id_guru, id_mapel, bobot, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tgl_input, jml_benar, jml_salah) VALUES ";
           
            $strq .= implode(",", $data).";";
            //echo $strq;
            //exit;

            $this->db->query($strq);
        } else {
            exit('Bukan File Excel...');//pesan error tipe file tidak tepat
        }
        redirect('adm/m_soal');
    }
	
}
