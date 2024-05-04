<template>
  <div>
    <h1>Test</h1>
  </div>
  <div>
    {{ email }}
  </div>
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
          this.email = response.data.email
        })
        .catch(error => {
          console.log(error)
        })
    }
  },
  mounted() {
    this.fetchEmail()
  }
}
</script>