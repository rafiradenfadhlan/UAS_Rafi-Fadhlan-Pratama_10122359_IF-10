<?php
session_start();
include 'koneksi.php';

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Create (Tambah Data)
if (isset($_POST['tambah'])) {
    if ($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $nama = htmlspecialchars($_POST['nama']);
        $nip = htmlspecialchars($_POST['nip']);
        $jabatan = htmlspecialchars($_POST['jabatan']);
        $gaji = floatval($_POST['gaji']);
        $tanggal_masuk = $_POST['tanggal_masuk'];

        $stmt = $koneksi->prepare("CALL tambah_pegawai(?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $nama, $nip, $jabatan, $gaji, $tanggal_masuk);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?status=success&action=tambah");
        exit;
    }
}

// Update (Edit Data)
if (isset($_POST['update'])) {
    if ($_POST['csrf_token'] === $_SESSION['csrf_token']) {
        $id = intval($_POST['id']);
        $nama = htmlspecialchars($_POST['nama']);
        $nip = htmlspecialchars($_POST['nip']);
        $jabatan = htmlspecialchars($_POST['jabatan']);
        $gaji = floatval($_POST['gaji']);
        $tanggal_masuk = $_POST['tanggal_masuk'];

        $stmt = $koneksi->prepare("UPDATE pegawai SET nama=?, nip=?, jabatan=?, gaji=?, tanggal_masuk=? WHERE id=?");
        $stmt->bind_param("sssdsi", $nama, $nip, $jabatan, $gaji, $tanggal_masuk, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?status=success&action=edit");
        exit;
    }
}

// Delete (Hapus Data)
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $koneksi->prepare("DELETE FROM pegawai WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $sql = "SELECT * FROM pegawai WHERE nama LIKE '%$search%' OR nip LIKE '%$search%' LIMIT $limit OFFSET $offset";
    $totalData = $koneksi->query("SELECT COUNT(*) AS total FROM pegawai WHERE nama LIKE '%$search%' OR nip LIKE '%$search%'")->fetch_assoc()['total'];
} else {
    $sql = "SELECT * FROM pegawai LIMIT $limit OFFSET $offset";
    $totalData = $koneksi->query("SELECT COUNT(*) AS total FROM pegawai")->fetch_assoc()['total'];
}
$totalPages = ceil($totalData / $limit);

$result = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Kepegawaian</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-center">Data Pegawai</h1>

        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <img src="employee.svg" alt="Logo Perusahaan" class="w-32 h-auto">
        </div>

        <!-- Form Tambah Data -->
        <form method="POST" id="addForm" class="space-y-4 mb-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="nama" placeholder="Nama" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="text" name="nip" placeholder="NIP" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="text" name="jabatan" placeholder="Jabatan" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="gaji" placeholder="Gaji" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="tanggal_masuk" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" name="tambah" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300">Tambah</button>
        </form>

        <!-- Tabel Data Pegawai -->
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">NIP</th>
                    <th class="px-4 py-2">Jabatan</th>
                    <th class="px-4 py-2">Gaji</th>
                    <th class="px-4 py-2">Tanggal Masuk</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="border-t">
                    <td class="px-4 py-2"><?= $row['id'] ?></td>
                    <td class="px-4 py-2"><?= $row['nama'] ?></td>
                    <td class="px-4 py-2"><?= $row['nip'] ?></td>
                    <td class="px-4 py-2"><?= $row['jabatan'] ?></td>
                    <td class="px-4 py-2"><?= $row['gaji'] ?></td>
                    <td class="px-4 py-2"><?= $row['tanggal_masuk'] ?></td>
                    <td class="px-4 py-2 space-x-2">
                        <button onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes($row['nama']) ?>', '<?= addslashes($row['nip']) ?>', '<?= addslashes($row['jabatan']) ?>', <?= $row['gaji'] ?>, '<?= $row['tanggal_masuk'] ?>')" class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600 transition duration-300">Edit</button>
                        <a href="#" onclick="confirmDelete(<?= $row['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition duration-300">Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex justify-center mt-4 space-x-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Edit Data Pegawai</h2>
            <form id="editForm" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" id="editId" name="id">
                <input type="text" id="editNama" name="nama" placeholder="Nama" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" id="editNip" name="nip" placeholder="NIP" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" id="editJabatan" name="jabatan" placeholder="Jabatan" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="number" id="editGaji" name="gaji" placeholder="Gaji" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" id="editTanggalMasuk" name="tanggal_masuk" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-300">Batal</button>
                    <button type="submit" name="update" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Animation -->
    <div id="loading" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="text-white">Loading...</div>
    </div>

    <!-- SweetAlert2 Script -->
    <script>
        // Fungsi untuk membaca parameter query string dari URL
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Periksa status dan tampilkan notifikasi jika ada
        document.addEventListener('DOMContentLoaded', () => {
            const status = getQueryParam('status');
            const action = getQueryParam('action');

            if (status === 'success') {
                let message = '';
                if (action === 'tambah') {
                    message = 'Data berhasil ditambahkan!';
                } else if (action === 'edit') {
                    message = 'Data berhasil diperbarui!';
                }

                if (message) {
                    Swal.fire({
                        title: 'Sukses!',
                        text: message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        history.replaceState(null, '', window.location.pathname);
                    });
                }
            }
        });

        // Fungsi untuk membuka modal edit
        function openEditModal(id, nama, nip, jabatan, gaji, tanggal_masuk) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editNip').value = nip;
            document.getElementById('editJabatan').value = jabatan;
            document.getElementById('editGaji').value = gaji;
            document.getElementById('editTanggalMasuk').value = tanggal_masuk;
            document.getElementById('editModal').classList.remove('hidden');
        }

        // Fungsi untuk menutup modal edit
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Konfirmasi hapus dengan SweetAlert2
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `index.php?hapus=${id}`;
                }
            });
        }

        // Validasi formulir tambah dan edit
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const nama = form.querySelector('[name="nama"]').value;
                const nip = form.querySelector('[name="nip"]').value;
                const gaji = form.querySelector('[name="gaji"]').value;

                if (!nama || !nip || !gaji) {
                    e.preventDefault(); // Mencegah pengiriman formulir
                    Swal.fire({
                        title: 'Error!',
                        text: 'Semua field wajib diisi!',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    document.getElementById('loading').classList.remove('hidden');
                }
            });
        });
    </script>
</body>
</html>