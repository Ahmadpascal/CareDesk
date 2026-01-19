<script setup>
import { onMounted, ref, watch } from 'vue'
import { useTicketStore } from '@/stores/ticket'
import { storeToRefs } from 'pinia'
import { capitalize, debounce } from 'lodash'
import feather from 'feather-icons'
import { DateTime } from 'luxon'

const ticketStore = useTicketStore()
const { tickets } = storeToRefs(ticketStore)
const { fetchTickets } = ticketStore

// TODO: Create filters ref with search fields
// Hint: You'll need search, status, priority, and date
const filters = ref({
    search: '',
    status: '',
    priority: '',
    date: '',
})

// TODO: Implement watch effect on filters
// Hint: Use debounce and call fetchTickets with updated filters
watch(filters, debounce(async () => {
    await fetchTickets(filters.value)
}, 300), { deep: true })

// TODO: Implement onMounted hook
// Hint: Fetch initial tickets and initialize feather icons
onMounted(async () => {
    await fetchTickets()

    feather.replace()
})
</script>

<template>
    <div class="p-6">
        <!-- Filters and Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" v-model="filters.search" placeholder="Cari tiket..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <i data-feather="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
                    </div>
                    <!-- Status Filter -->
                    <select v-model="filters.status"
                        class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="open">Open</option>
                        <option value="on_progress">On Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <!-- Priority Filter -->
                    <select v-model="filters.priority"
                        class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Prioritas</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <!-- Date Filter -->
                    <select
                        class="border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Tanggal</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID Tiket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pelapor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prioritas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="ticket in tickets" :key="ticket.code" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                #{{ ticket.code }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-800">{{ ticket.title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img :src="`https://ui-avatars.com/api/?name=${ticket.user.name}&background=0D8ABC&color=fff`"
                                        :alt="ticket.user.name" class="w-6 h-6 rounded-full">
                                    <span class="ml-2 text-sm text-gray-800">{{ ticket.user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-medium rounded-full" :class="{
                                    'text-blue-700 bg-blue-100': ticket.status === 'open',
                                    'text-yellow-700 bg-yellow-100': ticket.status === 'onprogress',
                                    'text-green-700 bg-green-100': ticket.status === 'resolved',
                                    'text-red-700 bg-red-100': ticket.status === 'rejected'
                                }">
                                    {{ capitalize(ticket.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-medium rounded-full" :class="{
                                    'text-red-700 bg-red-100': ticket.priority === 'high',
                                    'text-yellow-700 bg-yellow-100': ticket.priority === 'medium',
                                    'text-green-700 bg-green-100': ticket.priority === 'low'
                                }">
                                    {{ capitalize(ticket.priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ DateTime.fromISO(ticket.created_at).toFormat('dd MMMM yyyy HH:mm') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <RouterLink :to="{ name: 'admin.ticket.detail', params: { code: ticket.code } }"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i data-feather="message-square" class="w-4 h-4 mr-2"></i>
                                    Jawab
                                </RouterLink>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>