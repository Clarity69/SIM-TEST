<x-app-layout title="Edit {{ $mahasiswa->nama }}" :breadcrumbs="['Data Mahasiswa' => route('mahasiswa.index'), $mahasiswa->nama => route('mahasiswa.show', $mahasiswa), 'Edit' => null]">
    <x-page-header title="Edit Mahasiswa" description="Perbarui data {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }})." />
    @include('mahasiswa._form', ['action' => route('mahasiswa.update', $mahasiswa)])
</x-app-layout>
