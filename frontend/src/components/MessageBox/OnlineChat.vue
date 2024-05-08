<template>
  <div class="chat-container">
    <div class="messages-container" ref="messagesContainer">
      <div v-for="(message, index) in messages" :key="index" :class="{ 'sender-message': isSender(message.fromName), 'receiver-message': !isSender(message.fromName) }" :style="{ 'background-color': isSender(message.fromName) ? '#007bff' : '#f0f0f0' }" class="message">
        <div v-if="!isConsecutive(message.fromName, index)" class="message-sender">{{ message.fromName }} <span class="message-time">{{ formatMessageTime(message.date) }}</span></div>
        <div class="message-content">{{ message.message }}</div>
      </div>
    </div>
    <div class="new-message-container">
      <textarea v-model="newMessage" class="form-control" rows="3" placeholder="Type your message..."></textarea>
      <button @click="sendMessage" class="btn btn-primary send-button">Send</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    selectedUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      currentUser: '',
      isScrolledDown: true
    };
  },
  watch: {
    selectedUser: {
      immediate: true,
      handler(newValue, oldValue) {
        if (newValue !== oldValue) {
          this.isScrolledDown = true;
          this.fetchMessages();
        }
      }
    }
  },
  methods: {
    fetchMessages() {
      let data = new FormData();
      let sessionId = sessionStorage.getItem('sessionId');
      data.append('sessionId', sessionId);
      data.append('userName', this.selectedUser.username);
      axios.post('http://127.0.0.1:8000/messengerApi/fetch-messages', data)
          .then(response => {
            this.messages = response.data;
            if (this.isScrolledDown) {
              this.$nextTick(() => {
                this.scrollToBottom();
              });
              this.isScrolledDown = false;
            }
          })
          .catch(error => {
            console.error('Error fetching messages:', error);
          });
    },

    sendMessage() {
      let data = new FormData();
      let sessionId = sessionStorage.getItem('sessionId');
      data.append('sessionId', sessionId);
      data.append('message', this.newMessage);
      axios.post('http://127.0.0.1:8000/messengerApi/send-message', data)
          .then(response => {
            console.log(response.data);
            if (response.data.success) {
              this.isScrolledDown = true;
              this.fetchMessages();
              this.newMessage = '';
            }
          })
          .catch(error => {
            console.error('Error sending message:', error);
          });
    },
    isSender(fromName) {
      return fromName === this.currentUser;
    },
    isConsecutive(fromName, index) {
      if (index > 0) {
        const previousMessage = this.messages[index - 1];
        const currentMessage = this.messages[index];
        const previousTime = new Date(previousMessage.date);
        const currentTime = new Date(currentMessage.date);
        const timeDifference = Math.abs(currentTime - previousTime) / (1000 * 60);
        return fromName === previousMessage.fromName && timeDifference <= 5;
      }
      return false;
    },
    scrollToBottom() {
      this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
    },
    formatMessageTime(time) {
      const messageTime = new Date(time);
      const options = {hour: 'numeric', minute: 'numeric', hour12: true};
      return messageTime.toLocaleString('en-US', options);
    }
  },
  mounted() {
    this.fetchInterval = setInterval(() => {
      this.fetchMessages();
    }, 500);
    let data = new FormData();
    let sessionId = sessionStorage.getItem('sessionId');
    data.append('sessionId', sessionId);
    axios.post('http://127.0.0.1:8000/messengerApi/fetch-messages', data)
        .then(response => {
          if (response.data.success) {
            this.currentUser = response.data.username;
          }
        })
        .catch(error => {
          console.error('Error sending message:', error);
        });

  },
  beforeUnmount() {
    clearInterval(this.fetchInterval);
  }
};
</script>

<style scoped>
.sender-message {
  text-align: right;
  margin-right: 10px;
}

.receiver-message {
  text-align: left;
  margin-left: 10px;
}

.message {
  margin-bottom: 10px;
  padding: 5px;
  border-radius: 5px;
}

.message-sender {
  font-weight: bold;
}

.message-time {
  font-size: 0.8em;
}

.messages-container {
  overflow-y: auto;
  max-height: 400px; /* Set your desired maximum height */
}

.new-message-container {
  margin-top: 20px;
}
</style>
