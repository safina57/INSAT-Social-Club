<template>
    <div class="table-container">
        <table class="table table-dark table-hover table-striped">
            <thead>
                <tr>
                    <th>report_ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="reports.length === 0">
                  <td colspan="5" class="Note">No Reports found! You have a day off then ;)</td>
                </tr>
                <tr v-else v-for="report in reports" :key="report.id">
                    <td>{{ report.id }}</td>
                    <td>{{ report.username }}</td>
                    <td>{{ report.email }}</td>
                    <td>
                        <button @click="showContent(report)" class="btn btn-outline-secondary">Read Content</button>
                    </td>
                    <td>
                        <button @click="deleteReport(report)" class="btn btn-outline-secondary">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import axios from 'axios';
    export default {
        data() {
            return {
                reports: []
            }
        },
        methods: {
            deleteReport(report) {
                axios.post(`http://127.0.0.1:8000/admin_api/deleteRow/report/${report.id}`)
                    .then(response => {
                        if(response.data.success) {
                          alert("Report Deleted Successfully!")
                          this.fetchReports();
                        }
                    })
                    .catch(error => {
                        console.error('Error Deleting Report:', error);
                    });
            },
            showContent(report) {
                alert(report.content);
            },
            fetchReports(){
                function transformReport(report) {

                    return {
                        id: report.id,
                        fullname: report.fullname,
                        email: report.email,
                        content: report.message
                    };
                }

                axios.get(`http://127.0.0.1:8000/admin_api/getAll/report`)
            .then(response => {

                let result = response.data;
                result = result.map(report=>transformReport(report));
                this.reports = result;

            })
            .catch(error => {
                console.error('Error fetching reports:', error);
      });
            }
        },
        mounted() {
            this.fetchReports();
        },
        name: 'reportSection'
    }
</script>


<style>
    .table {
        width: 90%;
        margin: 10px auto 20px;
    }
    .table-container {
        margin-right: 40px;
        background-color: #A37A74;
        color: black;
        padding: 20px;
        border-radius: 20px;
    }
</style>