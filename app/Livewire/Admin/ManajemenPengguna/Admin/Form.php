<?php

namespace App\Livewire\Admin\ManajemenPengguna\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
      public $admin_id = null;
      public $status = 'aktif'; 

      public $nama;
      public $jenis_kelamin;
      public $email;
      public $password;

         protected function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            
            'jenis_kelamin' => 'required|in:L,P',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->admin_id),
            ],
           
            'password' => $this->admin_id
                ? 'nullable|min:6'
                : 'required|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    protected $messages = [
        'nama.required'    => 'Nama wajib diisi.',
        'jenis_kelamin.required'    => 'Jenis Kelamin wajib diisi.',
        'email.required'    => 'Email wajib diisi.',
        'status.required'    => 'Status wajib diisi.',
        'email.unique'     => 'Email ini sudah dipakai oleh pengguna lain.',
        'password.min'     => 'Password minimal 6 karakter.',
        'password.required'     => 'Password Wajib diisi.',
    ];

    public function loadData($id = null){
 $this->reset();
        $this->resetValidation();
        $this->status = 'aktif';
     if ($id) {
            $data = User::where('role', 'admin')->find($id);
            
            if ($data) { 
                $this->admin_id = $data->id;
                $this->nama = $data->name;
                $this->email = $data->email;
                $this->status = $data->status;
                $this->jenis_kelamin = $data->jenis_kelamin;
            }
        }
    }

     public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            if ($this->admin_id) {
              
                $user = User::findOrFail($this->admin_id);
                

           $userData = [
                'name'   => $this->nama,
                'email'  => $this->email,
                'status' => $this->status,
                'jenis_kelamin' => $this->jenis_kelamin,
            ];

            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }
                $user->update($userData);

                $message = 'Data Admin berhasil diperbarui.';

            } else {
            
                $user = User::create([
                    'name'     => $this->nama,
                    'email'    => $this->email,
                    'status'   => $this->status,
                     'jenis_kelamin' => $this->jenis_kelamin,
                    'password' => Hash::make($this->password),
                    'role' => 'admin',
                ]);


                $message = 'Data Admin berhasil ditambahkan.';
            }

            DB::commit();

            $this->dispatch('close-modal');
            $this->dispatch('refresh-admin'); 
            $this->dispatch('swal:success', [
                'title' => 'Berhasil!',
                'text'  => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('swal:error', [
                'title' => 'Gagal Menyimpan!',
                'text'  => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        return view('livewire.admin.manajemen-pengguna.admin.form');
    }
}
