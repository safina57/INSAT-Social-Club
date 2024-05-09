<template>
  <div class="inbox-container">
    <ul class="list-group">
      <li class="list-group-item inbox-header">
        Inbox
      </li>
      <li class="input-group mb-3">
        <searchBar :users="users"/>
      </li>
      <li v-for="(user, index) in users" :key="index" @click="selectUser(user)" @dblclick="redirectToProfile(user)">
        <div class="userBox">
          <img :src="user.avatar" alt="User Image" class="user-avatar">
          <div class="user-details">
            <span class="username">{{ user.username }}</span>
            <span class="user-status" :class="{ 'online': user.userStatus === 'Online', 'offline': user.userStatus === 'Offline' }">
              {{ user.userStatus }}
            </span>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "axios";
import searchBar from '@/components/searchBar.vue';

export default {
  mounted() {
    this.fetchUsers();
    this.fetchInterval = setInterval(() => {
      this.fetchUsers();
    }, 500);
  },
  beforeUnmount() {
    clearInterval(this.fetchInterval);
  },
  data() {
    return {
      search: '',
      users: []
    }
  },
  methods: {
    selectUser(user) {
      this.$emit('user-selected', user);
      if (this.$route.path === '/Home') {
        this.$router.push('/Messages');
      }
    },
    redirectToProfile(user) {
      this.$router.push(`/profile?User_ID=${user.userID}`);
    },
    fetchUsers() {
      function transformUserData(user) {
        return {
          userID: user.userID,
          username: user.username,
          avatar: user.img ? require(`../../../../backend/avatars/${user.img}`) : require(`../../../public/img/noProfileImage.jpg`),
          userStatus: user.userStatus
        };
      }
      const sessionId = sessionStorage.getItem('sessionId');
      let data = new FormData();
      data.append('sessionId', sessionId);
      axios.post('http://127.0.0.1:8000/messengerApi/all-users', data)
          .then(response => {
            let result = response.data;
            result = result.map(user => transformUserData(user));
            this.users = result;
            this.$emit('users-fetched', response.data);
          })
          .catch(error => {
            console.error('Error fetching users:', error);
          });
    }
  },
  name: 'InBox',
  components: {
    searchBar
  }
}
</script>

<style scoped>
.inbox-container {
  max-width: 400px;
  margin: 0 auto;
}

.list-group {
  padding: 0;
  margin: 0;
  list-style: none;
}

.list-group-item {
  background-color: #fafafa;
  border: none;
  border-radius: 8px;
  margin-bottom: 10px;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.list-group-item:hover {
  background-color: #f0f0f0;
}

.userBox {
  display: flex;
  align-items: center;
  padding: 10px;
  background-color: #fff;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.userBox:hover {
  background-color: #f5f5f5;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
  object-fit: cover;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.username {
  font-weight: bold;
  color: #333;
}

.user-status {
  margin-top: 5px;
  color: #666;
  font-size: 14px;
}

.online {
  color: #4caf50; /* Green */
}

.offline {
  color: #f44336; /* Red */
}

.input-group {
  width: 100%;
}

.input-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.input-group input:focus {
  border-color: #80bdff;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}
</style>
