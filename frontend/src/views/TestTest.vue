<template>
  <div>
    <h1>Test</h1>
  </div>
  <div>
    {{ email}}
    {{ posts}}

  </div>
  <button @click="changeEmail">Send Email</button>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      email: '',
      posts : ''
    }
  },
  methods: {
    fetchEmail() {
      axios.get('http://127.0.0.1:8000/admin_api/getAll/Post')
        .then(response => {
          this.email = response.data
          console.log(response.data)
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
    },

    fetchPosts() {
         axios.get('http://127.0.0.1:8000/homepage/getAllPosts')
           .then(response => {
             this.posts = response.data
             console.log('Posts', this.posts)
           })
           .catch(error => {
             console.log(error)
           })
       }

  },
  mounted() {
    this.fetchPosts()
    this.fetchEmail()
  }
}
</script>