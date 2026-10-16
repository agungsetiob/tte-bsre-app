<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { UserCog } from 'lucide-vue-next';
import { watch } from 'vue';

const props = defineProps<{ 
    show: boolean; 
    user: any; 
    departments: Array<{id: number, name: string}>;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    _method: 'put',
    department_id: '',
});

// Isi form otomatis saat modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen && props.user) {
        form.department_id = props.user.department_id || '';
        form.clearErrors();
    }
});

const submit = () => {
    form.post(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                    <UserCog class="h-5 w-5" />
                </span>
                <div>
                    <h3 class="font-display text-lg font-bold text-slate-800">Edit Data Pegawai</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Atur unit kerja untuk {{ user?.name }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="mt-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Unit Kerja (Department)</label>
                    <select v-model="form.department_id" class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:ring-0">
                        <option value="">-- Belum Diatur --</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.department_id" class="mt-1 text-xs text-rose-600">{{ form.errors.department_id }}</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 mt-2">
                    <button type="button" @click="emit('close')" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-[#0B8570] px-4 py-2 text-sm font-semibold text-white hover:bg-[#086B5B] disabled:opacity-50">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>