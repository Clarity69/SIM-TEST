<style>
        @page { margin: 1.5cm 1.8cm 1.8cm 1.8cm; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #000;
        }

        /* ===== KOP ===== */
        /* Kop = 3 kolom dengan lebar tetap: logo | teks | kolom kosong selebar logo,
           sehingga teks berada tepat di tengah halaman */
        .kop { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .kop td { vertical-align: middle; padding: 0; }
        .kop .logo { width: 15%; text-align: center; }
        .kop .logo img { width: 72px; height: 72px; }
        .kop .teks { width: 70%; text-align: center; }
        .kop .penyeimbang { width: 15%; }
        .kop .yayasan { font-size: 12pt; font-weight: bold; letter-spacing: .5px; }
        .kop .instansi { font-size: 18pt; font-weight: bold; letter-spacing: 1px; margin: 2px 0; }
        .kop .fakultas { font-size: 13pt; font-weight: bold; }
        .kop .alamat { font-size: 9.5pt; margin-top: 4px; }

        .garis-kop {
            border: 0;
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 8px 0 18px 0;
        }

        /* ===== JUDUL ===== */
        .judul { text-align: center; margin-bottom: 14px; }
        .judul h1 { font-size: 14pt; margin: 0; text-decoration: underline; letter-spacing: .5px; }
        .judul p { margin: 3px 0 0 0; font-size: 11pt; }

        .info { width: 100%; margin-bottom: 10px; font-size: 10.5pt; border-collapse: collapse; }
        .info td { padding: 1px 0; }
        .info .label { width: 120px; }
        .info .titik { width: 12px; }

        /* ===== TABEL DATA ===== */
        .data { width: 100%; border-collapse: collapse; font-size: 10pt; }
        .data th, .data td { border: 1px solid #000; padding: 5px 6px; }
        .data th { background: #e6e6e6; text-align: center; font-weight: bold; }
        .data td.tengah { text-align: center; }
        .data tr { page-break-inside: avoid; }
        .data thead { display: table-header-group; } /* header tabel diulang di tiap halaman */

        .kosong { text-align: center; font-style: italic; padding: 14px; }

        /* ===== RINGKASAN & TANDA TANGAN ===== */
        .ringkasan { margin-top: 10px; font-size: 10.5pt; }
        .ttd { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .ttd td { vertical-align: top; font-size: 11pt; }
        .ttd .kanan { width: 280px; text-align: center; }
        .ttd .ruang { height: 70px; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }

        /* ===== FOOTER NOMOR HALAMAN ===== */
        footer {
            position: fixed;
            bottom: -1.1cm;
            left: 0; right: 0;
            font-size: 8.5pt;
            color: #444;
            border-top: 1px solid #999;
            padding-top: 4px;
        }
        /* Pakai tabel, bukan float: float di elemen fixed membuat isi halaman bergeser di dompdf */
        footer table { width: 100%; border-collapse: collapse; }
        footer td { padding: 0; }
        footer .kanan { text-align: right; }
        footer .halaman:after { content: "Halaman " counter(page); }
    
        /* ===== DETAIL MAHASISWA ===== */
        .biodata { width: 100%; border-collapse: collapse; font-size: 11pt; margin-top: 6px; }
        .biodata td { padding: 5px 4px; vertical-align: top; border-bottom: 1px solid #ddd; }
        .biodata .label { width: 170px; }
        .biodata .titik { width: 12px; }
    </style>
