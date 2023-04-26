<template>
  <h2> Home </h2>
  <div v-if="!token">
  <form @submit.prevent="login" class="row justify-content-center g-3 mb-2">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Login</div>
        <div class="card-body">
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control mb-2" id="email" v-model="formData.email">
            <p class="text-danger" v-text="errors.email"></p>
          </div>
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control mb-2" id="password" v-model="formData.password">
            <p class="text-danger" v-text="errors.password"></p>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <button class="btn btn-primary">Sign in</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
  </div>
  <div v-else>
    <div class="d-flex justify-content-between mb-2">
      <div class="p-2">Hello, {{ currentUser.name }} </div>
      <button class="btn btn-danger btn-sm ml-auto p-2" @click="logout">Logout</button>
    </div></div>
</template>

<script>
export default {
  mounted() {
    if (this.token) this.getUser();
  },
  data() {
    return {
      token: localStorage.getItem('token') ?? null,
      currentUser: {},
      formData: {
        email:'',
        password:'',
      },
      errors: {},
    }
  },
  methods: {
    login(){
      fetch('api/login', {
        method: 'post',
        body: JSON.stringify( { email: this.formData.email, password: this.formData.password } ),
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN').slice(0,-2) }
      }).then(response => response.json()).then(response => {
        let errors = response.errors ?? null;
        if (errors) {
          let text = (errors.email ?? errors.password) ?? 'Bad credentials'
          this.errors.email = this.errors.password = text;
        } else {
          localStorage.setItem('token',response.token);
          this.errors = {}
          this.formData = { email:'', password:'',};
          this.$router.push('/users');
        }
      }).catch((err) => {console.log(err);});
    },
    logout(){
      fetch('api/logout', {
        method: 'post',
        headers: { 'Authorization':`Bearer ${this.token}`, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN').slice(0,-2) }
      }).then(response => response.json()).then(response => {
        this.token = null;
        localStorage.removeItem('token');
        this.$router.push('/');
      }).catch((err) => {console.log(err);});
    },
    getUser(){
      fetch('api/user',{ headers:{'Authorization':`Bearer ${this.token}`,'Accept':'application/json','Content-Type':'application/json'}}).then(
          response => response.json()).then( response => {
            this.currentUser.name = response.name ?? null;
            this.currentUser.email = response.email ?? null;
      })
    },
    getCookie(name){
      let cookie = {};
      document.cookie.split(';').forEach(function(el) {
        let [key,value] = el.split('=');
        cookie[key.trim()] = value;
      })
      return cookie[name];
    },
  }
};
</script>