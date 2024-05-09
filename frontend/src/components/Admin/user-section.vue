<template>
  <div>
    <div class="table-container">
      <table class="table table-striped table-dark table-hover">
        <thead>
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="users.length === 0">
          <td colspan="3" class="Note">No users found</td>
        </tr>
        <tr v-for="user in displayedUsers()" :key="user.User_ID">
<!--          <div v-if="user.Email !== 'insatsocialclubadm1n@gmail.com'">-->
            <td>{{ user.Username }}</td>
            <td>{{ user.Email }}</td>
            <td>
              <button v-if="user.Email !== 'insatsocialclubadm1n@gmail.com'" @click="deleteUser(user.User_ID)" class="btn btn-danger">Delete</button>
              <button v-else class="btn btn-danger disabled">Can't Delete Admin</button>
            </td>
<!--          </div>-->
        </tr>
        <tr v-if="showMoreButton()">
          <td colspan="3">
            <button @click="loadMoreUsers()" class="btn btn-primary">Show More</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';


export default {
  data() {
    return {
      users: [],
      displayedUsersCount: 5,
      chart: null
    }
  },
  methods: {
    deleteUser(userId) {
      axios.post(`http://127.0.0.1:8000/admin_api/deleteRow/user/${userId}`)
          .then(response => {
            if (response.data.success) {
              this.fetchUsers();
              alert('User deleted successfully!')
            } else {
              alert('Failed to delete user.');
            }
          })
          .catch(error => {
            console.error('Error deleting user:', error);
            alert('Error deleting user.');
          });
    },
    loadMoreUsers() {
      this.displayedUsersCount += 4;
    },
    fetchUsers() {
      axios.get(`http://127.0.0.1:8000/admin_api/getAll/user`)
          .then(response => {
            let result = response.data;
            result = result.map(user => this.transformData(user));
            if (result.length > 0) {
              console.log("Data fetched successfully");
              this.users = result;
            } else {
              console.log("No data found");
            }
          })
          .catch(error => {
            console.error('Error fetching users:', error);
            alert('Error fetching users.');
          });
    },
    transformData(user) {
      return {
        User_ID: user.id,
        Username: user.username,
        Email: user.email,
      };
    },
    displayedUsers() {
      return this.users.slice(0, this.displayedUsersCount);
    },
    showMoreButton() {
      return this.displayedUsersCount < this.users.length;
    },
  },
  mounted() {
    this.fetchUsers();
  },
}
</script>

<style scoped>
.table-container {
  margin-right: 40px;
  background-color: #9a8c98;
  color: white;
  padding: 20px;
  border-radius: 20px;
}


</style>
