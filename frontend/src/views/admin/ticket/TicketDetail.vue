<script setup>
import { onMounted, ref } from 'vue'
import { useTicketStore } from '@/stores/ticket'
import { storeToRefs } from 'pinia'
import { capitalize } from 'lodash'
import feather from 'feather-icons'
import { DateTime } from 'luxon'
import { useRoute } from 'vue-router'

const route = useRoute()

const ticket = ref({})
const form = ref({
    status: '',
    content: '',
})

const ticketStore = useTicketStore()
const { success, error, loading } = storeToRefs(ticketStore)
const { fetchTicket, createTicketReply } = ticketStore

const fetchTicketDetail = async () => {
    const response = await fetchTicket(route.params.code)

    ticket.value = response
    form.value.status = response.status
}

// TODO: Implement handleSubmit function
// Hint: This should call createTicketReply with code and form
// Then refetch ticket details
const handleSubmit = async () => {
    await createTicketReply(route.params.code, form.value)

    await fetchTicketDetail()
}

// TODO: Implement onMounted hook
// Hint: Fetch initial ticket details and initialize feather icons
onMounted(async () => {
    await fetchTicketDetail()

    feather.replace()
})
</script>

<template>
    <div class="p-6">
        <!-- Ticket Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ ticket.title }}</h3>
                        <div class="mt-4 flex items-center space-x-4">
                            <span class="px-3 py-1 text-sm  rounded-lg" :class="{
                                'text-blue-700 bg-blue-100': ticket.status === 'open',
                                'text-yellow-700 bg-yellow-100': ticket.status === 'onprogress',
                                'text-green-700 bg-green-100': ticket.status === 'resolved',
                                'text-red-700 bg-red-100': ticket.status === 'rejected'
                            }">
                                {{ capitalize(ticket.status) }}
                            </span>

                            <span class="px-3 py-1 text-sm rounded-lg" :class="{
                                'text-red-700 bg-red-100': ticket.priority === 'high',
                                'text-yellow-700 bg-yellow-100': ticket.priority === 'medium',
                                'text-green-700 bg-green-100': ticket.priority === 'low'
                            }">
                                {{ capitalize(ticket.priority) }}
                            </span>

                            <span class="text-sm text-gray-500">Dilaporkan oleh {{ ticket.user?.name }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-4">
                        <button
                            class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            <i data-feather="download" class="w-4 h-4 inline-block mr-2"></i>
                            Lampiran
                        </button>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            <i data-feather="check-circle" class="w-4 h-4 inline-block mr-2"></i>
                            Selesaikan Tiket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discussion Thread -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div v-for="reply in ticket.ticket_replies" class="p-6 border-b border-gray-100"
                v-if="ticket.ticket_replies?.length > 0">
                <div class="flex items-start space-x-4">
                    <img :src="`https://ui-avatars.com/api/?name=${reply.user.name}&background=0D8ABC&color=fff`"
                        :alt="reply.user.name" class="w-10 h-10 rounded-full">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-800">{{ reply.user.name }}</h4>
                                <p class="text-xs text-gray-500">
                                    {{ DateTime.fromISO(reply.created_at).toFormat('dd MMMM yyyy, HH:mm') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 text-sm text-gray-800">
                            <p>{{ reply.content }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="p-6">
                    <p class="text-sm text-gray-500">Belum ada tanggapan</p>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100">
                <h4 class="text-sm font-medium text-gray-800 mb-4">Tambah Jawaban</h4>
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Tiket</label>
                            <select v-model="form.status"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="open" class="text-blue-700">Open</option>
                                <option value="onprogress" class="text-yellow-700">On Progress</option>
                                <option value="resolved" class="text-green-700">Resolved</option>
                                <option value="rejected" class="text-red-700">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <textarea v-model="form.content"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            :class="{ 'border-red-500 ring-red-500': error?.content }" rows="4"
                            placeholder="Tulis jawaban Anda di sini..."></textarea>
                        <p class="mt-1 text-xs text-red-500" v-if="error?.content">
                            {{ error?.content?.join(', ') }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button type="button"
                                class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                                <i data-feather="paperclip" class="w-4 h-4 inline-block mr-2"></i>
                                Lampiran
                            </button>
                        </div>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                            <i data-feather="send" class="w-4 h-4 inline-block mr-2"></i>
                            <span v-if="!loading">
                                Kirim Jawaban
                            </span>
                            <span v-else>
                                Loading...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</template>