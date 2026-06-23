<?php

namespace App\Livewire\Admin\ManajemenPengguna\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    #[Layout('layouts.app')]
    #[Title('Manajemen Admin')]

  
   

      #[On('refresh-admin')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    #[On('hapus-data-admin')]
    public function hapusDataAdmin($id)
    {
        $admin = User::where('role', 'admin')->find($id);

        if (!$admin) {
            $this->dispatch('swal:error', ['title' => 'Gagal!', 'text' => 'Data Admin tidak ditemukan.']);
            return;
        }

        if ($admin->id === Auth::user()->id) {
    $this->dispatch('swal:error', [
        'title' => 'Akses dibatasi!',
        'text'  => 'Anda tidak dapat menghapus akun sendiri.'
    ]);
    return;
}

        DB::beginTransaction();

        try {
            $namaAdmin = $admin->name;
         
     
        $admin->delete();

        DB::commit();

        $this->dispatch('swal:success', [
            'title' => 'Berhasil Dihapus!',
            'text'  => "Data admin {$namaAdmin} berhasil dihapus."
        ]);

        $this->dispatch('refresh-admin');

    } catch (\Exception $e) {
        DB::rollBack();
        $this->dispatch('swal:error', [
            'title' => 'Error Sistem!',
            'text'  => 'Gagal menghapus data admin: ' . $e->getMessage()
        ]);
    }
}
    
    public function render()
    {
        $admins = User::where('role' , 'admin')->paginate(10);
        return view('livewire.admin.manajemen-pengguna.admin.index',
        ['admins' => $admins]);
    }
}
