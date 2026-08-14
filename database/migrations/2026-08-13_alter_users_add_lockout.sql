-- URS #2: lockout akun setelah 3x gagal login berturut-turut.
-- Counter reset cuma pas login sukses (pola bank/ATM), bukan berdasar window waktu.
-- Yang bisa unblock cuma Administrator/Supervisor (role dengan akses menu Users),
-- lewat ganti account_status balik ke 'Active' di halaman Users.
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `failed_login_attempts` int(11) DEFAULT 0;
