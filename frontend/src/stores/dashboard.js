import { defineStore } from "pinia";
import { axiosInstance } from '@/plugins/axios';
import { handleError } from "@/helpers/errorHelper";

export const useDashboardStore = defineStore("dashboard", {
    state: () => ({
        statistic: null,
        loading: false,
        error: null,
        success: null,
    }),

    actions: {
        async fetchStatistics() {
            this.loading = true

            try {
                const response = await axiosInstance.get(`dashboard/statistics`)

                this.statistic = response.data.data
            } catch (error) {
                this.error = handleError(error)
            } finally {
                this.loading = false
            }
        }
    }
})