<script setup>
import { computed, onMounted } from 'vue';
import useTranslations from '@/Composables/useTranslations';

const props = defineProps({
    permissions: { type: Array, required: true },
    modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const { t, load } = useTranslations();

onMounted(() => {
    const keys = props.permissions.map((p) => 'permissions.' + p);
    load(keys);
});

const GROUP_LABELS = {
    roles: 'Rôles',
    users: 'Utilisateurs',
    vehicles: 'Véhicules',
    reservations: 'Réservations',
    allowed_domains: 'Domaines autorisés',
    vehicle_suggestion: 'Suggestion de véhicule',
};

const grouped = computed(() => {
    const map = {};
    for (const p of props.permissions) {
        const [module] = String(p).split('.');
        if (!map[module]) map[module] = [];
        map[module].push(p);
    }
    return Object.entries(map).map(([module, perms]) => ({
        module,
        label: GROUP_LABELS[module] || module,
        permissions: perms.sort(),
    }));
});

const totalCount = computed(() => props.permissions.length);
const selectedCount = computed(() => props.modelValue.length);

const isAllChecked = computed(() => selectedCount.value > 0 && selectedCount.value >= totalCount.value);
const isNoneChecked = computed(() => selectedCount.value === 0);

function isGroupFullyChecked(perms) {
    return perms.every((p) => props.modelValue.includes(p));
}

function toggle(permission) {
    const next = props.modelValue.includes(permission)
        ? props.modelValue.filter((p) => p !== permission)
        : [...props.modelValue, permission];
    emit('update:modelValue', next);
}

function toggleGroup(perms, checked) {
    let next = [...props.modelValue];
    if (checked) {
        for (const p of perms) {
            if (!next.includes(p)) next.push(p);
        }
    } else {
        next = next.filter((p) => !perms.includes(p));
    }
    emit('update:modelValue', next);
}

function toggleAll(checked) {
    emit('update:modelValue', checked ? [...props.permissions] : []);
}
</script>

<template>
    <div class="space-y-5">
        <!-- En-tête : tout cocher / tout décocher + résumé -->
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="toggleAll(true)"
                    :disabled="isAllChecked"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Tout cocher
                </button>
                <span class="text-gray-400">|</span>
                <button
                    type="button"
                    @click="toggleAll(false)"
                    :disabled="isNoneChecked"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Tout décocher
                </button>
            </div>
            <span class="text-sm text-gray-600">
                {{ selectedCount }} / {{ totalCount }} permission{{ totalCount > 1 ? 's' : '' }} sélectionnée{{ selectedCount !== 1 ? 's' : '' }}
            </span>
        </div>

        <!-- Cartes par module -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div
                v-for="g in grouped"
                :key="g.module"
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
            >
                <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="font-semibold text-gray-800">{{ g.label }}</h3>
                    <div class="flex gap-1">
                        <button
                            type="button"
                            @click="toggleGroup(g.permissions, true)"
                            :disabled="isGroupFullyChecked(g.permissions)"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800 disabled:opacity-50"
                        >
                            Tous
                        </button>
                        <span class="text-gray-300">·</span>
                        <button
                            type="button"
                            @click="toggleGroup(g.permissions, false)"
                            :disabled="g.permissions.every((p) => !modelValue.includes(p))"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800 disabled:opacity-50"
                        >
                            Aucun
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <label
                        v-for="p in g.permissions"
                        :key="p"
                        class="flex cursor-pointer items-center gap-2 rounded py-1.5 px-2 text-sm transition hover:bg-gray-50"
                    >
                        <input
                            type="checkbox"
                            :checked="modelValue.includes(p)"
                            @change="toggle(p)"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="text-gray-700">{{ t('permissions.' + p) }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>
