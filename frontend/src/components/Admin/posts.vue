<template>
    <div class="table-container">
        <table class="table table-striped table-dark table-hover">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Content</th>
                    <th>Media</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="Posts.length === 0">
                    <td colspan="5" class="Note">No Posts found</td>
                </tr>
                <tr v-for="(post,index) in displayedPosts" :key="post.Post_ID">
                    <td>{{ post.Username }}</td>
                    <td>
                        <button @click="showContent(index)" class="btn btn-outline-secondary">Read Content</button>
                    </td>
                    <td>
                        <button @click="showMedia(post.Media)" class="btn btn-outline-secondary">View Media</button>
                    </td>
                    <td>
                        <button @click="showComments(index)" class="btn btn-outline-secondary">View Comments</button>
                    </td>
                    <td>
                        <button @click="deletePost(post.Post_ID)" class="btn btn-danger">Delete</button>
                    </td>
                </tr>
                <tr v-if="showMoreButton">
                    <td colspan="5">
                        <button @click="loadMorePosts()" class="btn btn-primary">Show More</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div v-if="!isNaN(mediaShown) && Posts[mediaShown] && Posts[mediaShown].Media">
                {{ Posts[mediaShown].Media }}
            </div>
        </div>
        <div class="col-sm-6">
            <div v-if="!isNaN(commentsShown) && Posts[commentsShown] && Posts[commentsShown].Comments">
                <div v-for="comment in Posts[commentsShown].Comments" :key="comment.comment_ID">
                    <p>{{ comment.username }}</p>
                    <p>{{ comment.Content }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="image-container" v-if="mediaShown">
        <img :src="mediaShown" alt="Media Image" style="max-width: 100%; height: auto;">
    </div>
    <div class="image-container" v-else>
        <p v-if="viewMedia">No Media to show</p>
    </div>
</template>


<script>
import axios from 'axios';
    export default {
      data() {
        return {
          Posts: [],
          displayedPostsCount: 8,
          mediaShown: "",
          commentsShown: NaN,
          viewMedia: false
        }
      },
      computed: {
        displayedPosts() {
          return this.Posts.slice(0, this.displayedPostsCount);
        },
        showMoreButton() {
          return this.displayedPostsCount < this.Posts.length;
        }
      },
      methods: {
        deletePost(Post_ID) {
          let data = new FormData();
          data.append('Post_ID', Post_ID);
          axios.post(`http://127.0.0.1:8000/admin_api/deleteRow/post/${Post_ID}`)
              .then(response => {
                if (response.data.success) {
                  this.fetchPosts();
                  alert(`Post ${Post_ID} has been deleted successfully!`);
                }
              })
              .catch(error => {
                console.error('Error Deleting Post:', error);
                alert('Error Deleting Post')
              });

        },
        showContent(index) {
          alert(this.Posts[index].Content);
        },
        showMedia(media) {
          this.viewMedia = true;
          if (media) {
            this.mediaShown = "media";
          } else {
            this.mediaShown = "";
          }
        },
        showComments(index) {
          if (this.commentsShown !== index) {
            this.commentsShown = index;
          } else {
            this.commentsShown = NaN;
          }
        },
        loadMorePosts() {
          this.displayedPostsCount += 5;
        },
        fetchPosts() {
          function transformPost(post) {
                  return {
                    Username: post.User.username,
                    Content: post.caption,
                    Media: post.media,
                    Comments: post.comments,
                    Post_ID: post.id
                  };
                }

          axios.get(`http://127.0.0.1:8000/admin_api/getAll/Post`)
              .then(response => {
                let result = response.data;
                result = result.map(post => transformPost(post));
                if (result.length > 0)
                {
                  console.log("Data fetched successfully");
                  this.Posts = result ;
                }
              })
              .catch(error => {
                console.error('Error fetching posts:', error);
              })
        }
      },
      mounted() {
        this.fetchPosts();
      },
      name: 'postSection',
    }
</script>


<style>
    .table {
        width: 90%;
        margin: 20px auto;
        margin-top:10px;
    }
    .table-container {
        margin-right: 40px;
        background-color: #9a8c98;
        color: white;
        padding: 20px;
        border-radius: 20px;
    }
    .image-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }
</style>