<template>
  <div>
    <h1>Test</h1>
  </div>
  <div>
    {{ email }}
  </div>
  <button @click="changeEmail">Send Email</button>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      email: ''
    }
  },
  methods: {
    fetchEmail() {
      axios.get('http://127.0.0.1:8000/api/login')
        .then(response => {
          this.email = response.data
        })
        .catch(error => {
          console.log(error)
        })
    },
    changeEmail(){
      let data =new FormData();
      data.append('email', this.email)
      axios.post('http://127.0.0.1:8000/api/login1', data)
          .then(response => {
            console.log(response.data)
          })
          .catch(error => {
                console.log(error)
              }
          )
    }
  },
  mounted() {
    this.fetchEmail()
  }
}
</script>