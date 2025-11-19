<template>
    <v-card flat>
        <v-card-title class="d-flex justify-space-between align-center">
            <span class="text-h6">Loans</span>
            <v-btn size="small" variant="tonal" :loading="loading" @click="loadLoans">
                Refresh
            </v-btn>
        </v-card-title>

        <v-data-table class="elevation-1" :headers="headers" :items-per-page-options="[25, 50, 100]"
            :items-per-page="25" :items="loans" :loading="loading">
            <template #item.loaned_at="{ item }">
                {{ moment(item.loaned_at).format('MMM Do YYYY \\a\\t h:mm A') }}
            </template>

            <template #item.returned_at="{ item }">
                {{ item.returned_at ? moment(item.returned_at).format('MMM Do YYYY \\a\\t h:mm A') : '-' }}
            </template>

            <template #item.due_at="{ item }">
                {{ moment(item.due_at).isAfter() ? 'overdue' : "due in" + moment(item.due_at).toNow() }}
            </template>

            <template #item.actions="{ item }">
                <div class="d-flex flex-nowrap">
                    <v-btn size="small" variant="text" icon="mdi-calendar"
                        :disabled="loading || item.returned_at !== null" @click="extendLoan(item.id)" />
                </div>
            </template>

            <template #loading>
                <v-sheet class="pa-4 text-center">Loading loans...</v-sheet>
            </template>
        </v-data-table>

        <v-dialog persistent v-model="dialog.open" max-width="640">
            <v-card>
                <v-card-title class="text-h6">Extend Loan</v-card-title>

                <v-card-text>
                    <v-form ref="extendForm" @submit.prevent="submitDialog">
                        <v-row dense>
                            <v-col cols="12" sm="6">
                                <v-select v-model.trim="dialog.form.additional_days" :items="items" density="compact"
                                    label="Additional Days" item_title="title" item-value="value"></v-select>

                            </v-col>
                        </v-row>

                        <button type="submit" class="d-none" />
                    </v-form>
                </v-card-text>

                <v-card-actions class="justify-end">
                    <v-btn variant="text" @click="closeDialog" :disabled="dialog.saving">Cancel</v-btn>
                    <v-btn color="primary" :loading="dialog.saving" @click="submitDialog">
                        Save
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script>
    import axios from 'axios';
import moment from 'moment';
import { toast } from 'vue3-toastify';

    export default {
        name: 'LoansTab',

        data() {
            return {
                moment,

                loading: false,
                loans: [],
                headers: [
                    { title: 'ID', key: 'id' },
                    { title: 'User', key: 'user.name' },
                    { title: 'Book', key: 'book.title' },
                    { title: 'Loan Date', key: 'loaned_at' },
                    { title: 'Return Date', key: 'returned_at' },
                    { title: 'Due Date', key: 'due_at' },
                    { title: 'Actions', key: 'actions' },
                ],
                items: [{ title: '1 day', value: 1 }, { title: '3 days', value: 3 }, { title: '7 days', value: 7 }, { title: '10 days', value: 10 }, { title: '14 days', value: 14 }],
                dialog: {
                    open: false,
                    saving: false,
                    form: {
                        id: '',
                        due_at: '',
                        additional_days: '',
                    },
                },
            };
        },

        methods: {
            loadLoans() {
                this.loading = true;

                return axios.get('/api/v1/loans')
                    .then(r => this.loans = r.data)
                    .catch(e => {
                        toast(e.response?.data?.message || e.response?.statusText || 'Error', { type: 'error' });
                        console.error(e);
                    })
                    .finally(() => this.loading = false);
            },
            dialogInit(form) {
                this.dialog.form = {
                    additional_days: '',
                    id: '',
                    due_at: ''
                };
            },

            extendLoan(id) {
                return axios.get(`/api/v1/loans/extend/${id}`)
                    .then(r => {
                        this.dialog.form = { id: r.data.id, due_at: r.data.due_at, additional_days: '' };
                        this.dialog.open = true;
                    })
                    .catch(e => {
                        toast(e.response?.data?.message || e.response?.statusText || 'Error', { type: 'error' });
                        console.error(e);
                    });
            },

            submitDialog() {
                if (this.dialog.saving) return;
                this.dialog.saving = true;

                const payload = {
                    additional_days: this.dialog.form.additional_days,
                };


                axios.put(`/api/v1/loans/${this.dialog.form.id}`, payload)
                    .then(() => {
                        toast('Loan extended', { type: 'success' });
                        this.loadLoans();
                        this.closeDialog();
                    })
                    .catch(e => {
                        toast(e.response?.data?.message || e.response?.statusText || 'Error', { type: 'error' });
                        console.error(e);
                    })
                    .finally(() => this.dialog.saving = false);
            },

            closeDialog() {
                this.dialog.open = false;
                this.dialogInit();
            },
        },

        mounted() {
            this.loadLoans();
        },
    };
</script>
