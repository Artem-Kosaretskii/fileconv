<template>
  <div class="d-flex justify-content-start mb-2">
    <h2 class="me-5">File upload for {{ currentUser.name }}</h2>
    <button class="btn btn-outline-danger btn-sm ml-auto p-2" @click="goBack">Back home</button>
  </div>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">File Upload</div>
          <div class="card-body">
            <div v-show="formData.success !== ''" class="alert alert-success">
              {{ formData.success }}
            </div>
            <form @submit="formSubmit" enctype="multipart/form-data">
              <div class="form-group mb-1">
                <input type="text" class="form-control" placeholder="Name" v-model="formData.name">
                <p class="text-danger" v-text="errors.name"></p>
              </div>
              <div class="form-group mb-1">
                <input type="text" class="form-control" placeholder="Last name" v-model="formData.lastName">
                <p class="text-danger" v-text="errors.lastName"></p>
              </div>
              <div class="form-group mb-1">
                <input type="email" class="form-control" placeholder="Email" v-model="formData.email">
                <p class="text-danger" v-text="errors.email"></p>
              </div>
              <div class="form-group mb-1">
                <input id="file" type="file" class="form-control" accept=".docx" v-on:change="onChange">
                <p class="text-danger" v-text="errors.file"></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  data() {

    return {
      currentUser: {},
      token: localStorage.getItem('token'),
      formData: { name: '', lastName: '', email: '', file: '', success: '', error: '' },
      errors: {}
    };

  },

  mounted(){

    fetch('api/user',{
      headers:{
        'Authorization':`Bearer ${this.token}`,
        'Accept':'application/json',
        'Content-Type':'application/json'}
    }).then(
        response => response.json()).then( response => {
          this.currentUser.id = response.id ?? null;
          this.currentUser.name = response.name ?? null;
          this.currentUser.email = response.email ?? null;
          if (!this.currentUser.name) { this.$router.push('/') }
    }).catch(err=>console.log(err))
  },

  methods: {

    onChange(event) {
      this.formData.file = event.target.files[0];
      this.formSubmit();
    },

    formSubmit() {
      let token = this.getCookie('XSRF-TOKEN') ?? '';
      if (token) token = token.slice(0,-2);
      const body = new FormData();
      body.append('file', this.formData.file);
      const name = this.formData.name ?? '';
      const last_name = this.formData.lastName ?? '';
      const email = this.formData.email ?? '';
      const user_id = this.currentUser.id ?? '';
      fetch(`api/upload?user_id=${user_id}&name=${name}&last_name=${last_name}&email=${email}`, {
        body,
        method: 'post',
        headers: {
          'Authorization':`Bearer ${this.token}`,
          //'Content-Type': 'multipart/form-data',
          'Accept': 'application/json',
          'X-XSRF-TOKEN': token }
      }).then(response => response.json()).then((response) => {
        const errors = response.errors ?? null;
        if (errors){
          if (errors.file && Array.isArray(errors.file)) {
            this.errors.file = errors.file.join(" ");
          } else {
            this.errors.file = "Your file doesn't fit to certain requirements";
          }
          if (errors.name) {
            this.errors.name = errors.name;
          }
          if (errors.lastName) {
            this.errors.lastName = errors.lastName;
          }
          if (errors.email) {
            this.errors.email = errors.email;
          }
        } else {
          this.errors = {};
        }
        this.formData.success = response.success;
        document.getElementById('file').value = '';
      }).catch((response) => {
        this.formData.error = response.error;
        document.getElementById('file').value = '';
      });
    },

    goBack(){
      this.$router.push('/')
    },

    getCookie(name){
      let cookie = {};
      document.cookie.split(';').forEach(function(el) {
        let [key,value] = el.split('=');
        cookie[key.trim()] = value;
      })
      return cookie[name];
    }
  }
}
</script>