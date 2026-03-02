export const authMixin = {
    methods: {
        async checkSession() {
            try {
                const res = await fetch('api.php?action=check_session');
                const data = await res.json();
                if (data.status === 'success') {
                    this.user = data.user;
                    this.isLocked = false;
                } else {
                    this.isLocked = true;
                }
            } catch (e) {
                console.error("Session Check Failed", e);
                this.isLocked = true;
            }
        },

        

        async unlockPage() {
            if (!this.username || !this.accessCode) return Swal.fire('Error', 'Masukkan Username dan Password', 'warning');
            
            this.isLoading = true;
            const res = await this.postToApi('login', { username: this.username, password: this.accessCode });
            this.isLoading = false;
            
            if (res.status === 'success') {
                this.isLocked = false;
                this.user = res.user;
                this.username = '';
                this.accessCode = ''; 
                this.showToast(`Selamat Datang, ${this.user.name}`);
            } else {
                Swal.fire('Akses Ditolak', 'Username atau Password salah!', 'error');
                this.accessCode = '';
            }
        },
        
        async logout() {
            await this.postToApi('logout', {});
            this.user = null;
            this.isLocked = true;
            this.showToast('Logout Berhasil', 'info');
        }
    }
};
