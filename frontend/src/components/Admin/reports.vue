<template>
  <div class="row justify-content-center">

    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                Total number of reports</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">{{reports.length}}</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-file fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                Number of Unique Reporters</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">{{uniqueReporters}}</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-person fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->

    <!-- Pending Requests Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                Most Active Reporter </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">{{mostActiveReporter}}</div>
            </div>
            <div class="col-auto">
              <i class="fas fa-comments fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="table-container">
        <table class="table table-dark table-hover table-striped">
            <thead>
                <tr>
                    <th>Report ID</th>
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
                    <td>{{ report.fullname }}</td>
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
      reports: [],
      uniqueReporters: 0,
      mostActiveReporter: ''
    }
  },
  methods: {
    deleteReport(report) {
      axios.post(`http://127.0.0.1:8000/admin_api/deleteRow/report/${report.id}`)
          .then(response => {
            if (response.data.success) {
              alert("Report Deleted Successfully!");
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
    fetchReports() {
      axios.get(`http://127.0.0.1:8000/admin_api/getAll/report`)
          .then(response => {
            const transformedReports = response.data.map(report => ({
              id: report.id,
              fullname: report.fullname,
              email: report.email,
              content: report.message
            }));
            this.reports = transformedReports;
            this.updateStatistics();
          })
          .catch(error => {
            console.error('Error fetching reports:', error);
          });
    },
    updateStatistics() {
      const emailSet = new Set(this.reports.map(report => report.email));
      this.uniqueReporters = emailSet.size;
      this.updateMostActiveReporter();
    },
    updateMostActiveReporter() {
      const emailCount = this.reports.reduce((acc, report) => {
        acc[report.email] = (acc[report.email] || 0) + 1;
        return acc;
      }, {});

      let maxCount = 0;
      let mostActiveEmail = '';
      for (const [email, count] of Object.entries(emailCount)) {
        if (count > maxCount) {
          maxCount = count;
          mostActiveEmail = email;
        }
      }


      const mostActiveReporter = this.reports.find(report => report.email === mostActiveEmail);
      this.mostActiveReporter = mostActiveReporter ? mostActiveReporter.fullname : 'No active reporters';
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
      border-radius: 10px;
    }
    .table-container {
        margin-right: 40px;
        background-color: #abcc55;
        color: black;
        padding: 20px;
        border-radius: 20px;
    }
</style>