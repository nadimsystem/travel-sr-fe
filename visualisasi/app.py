from flask import Flask, render_template, jsonify
import pandas as pd
import os
import numpy as np # Import numpy untuk menangani data

app = Flask(__name__)

# Menentukan path ke folder data
DATA_FOLDER = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'data')

# Fungsi untuk membersihkan nilai moneter
def clean_currency(value):
    try:
        # Mengonversi ke string, menghapus spasi, lalu konversi ke float
        return float(str(value).strip())
    except (ValueError, TypeError):
        # Jika konversi gagal (misalnya karena teks atau NaN), kembalikan 0
        return 0

# FUNGSI LAMA: untuk mendapatkan data Laba Rugi (tidak ada perubahan)
def get_laba_rugi_data():
    file_path = os.path.join(DATA_FOLDER, 'laba_rugi_bersih.csv')
    df = pd.read_csv(file_path)
    
    pendapatan_series = df[df['Nama Akun'] == 'TOTAL PENDAPATAN'].iloc[0, 2:]
    laba_series = df[df['Nama Akun'] == 'LABA BERSIH'].iloc[0, 2:]
    
    pendapatan = [clean_currency(p) for p in pendapatan_series.values]
    laba = [clean_currency(l) for l in laba_series.values]
    
    bulan = list(df.columns[2:])
    
    return {
        "labels": bulan,
        "pendapatan": pendapatan,
        "laba": laba
    }

# FUNGSI BARU: untuk mendapatkan data Neraca
def get_neraca_data():
    file_path = os.path.join(DATA_FOLDER, 'neraca_bersih.csv')
    df_neraca = pd.read_csv(file_path)
    
    # Ambil data untuk bulan terakhir yang tersedia (contoh: kolom paling kanan)
    # Kolom neraca seringkali berpasangan (Nilai, %), jadi kita ambil kolom nilai terakhir.
    # Kita asumsikan kolom nilai adalah kolom dengan indeks ganjil dari belakang.
    latest_month_column = df_neraca.columns[-2]

    # Ambil nilai total aset lancar dan aset tetap
    total_aset_lancar = df_neraca[df_neraca['Nama Akun'] == 'TOTAL ASET LANCAR'].iloc[0]
    total_aset_tetap = df_neraca[df_neraca['Nama Akun'] == 'TOTAL ASET TETAP'].iloc[0]

    # Bersihkan nilai dari kolom bulan terakhir
    aset_lancar_value = clean_currency(total_aset_lancar[latest_month_column])
    aset_tetap_value = clean_currency(total_aset_tetap[latest_month_column])

    return {
        "labels": ["Aset Lancar", "Aset Tetap"],
        "values": [aset_lancar_value, aset_tetap_value],
        "month": latest_month_column # Kirim nama bulan untuk judul grafik
    }

# Endpoint Laba Rugi (tidak ada perubahan)
@app.route('/api/laba-rugi')
def data_laba_rugi():
    data = get_laba_rugi_data()
    return jsonify(data)

# ENDPOINT BARU: untuk data Neraca
@app.route('/api/neraca')
def data_neraca():
    data = get_neraca_data()
    return jsonify(data)

# Halaman utama (tidak ada perubahan)
@app.route('/')
def index():
    return render_template('index.html')

if __name__ == '__main__':
    app.run(debug=True)