<template>

  <div class="d-flex justify-content-between mb-2">
    <div class="p-2">Current user: {{ currentUser.name }} , {{currentUser.email}}</div>
    <button class="btn btn-danger btn-sm ml-auto p-2" @click="logout">Logout</button>
  </div>

  <div class="d-flex justify-content-between mb-2">
    <h2 class="me-5"> Users </h2>
    <form @submit.prevent="searchById" class="d-flex justify-content-start me-5">
      <div class="form-group me-2">
        <input type="text" class="form-control" placeholder="User id" v-model="search_id">
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Find</button>
      </div>
    </form>

    <form @submit.prevent="searchByString" class="d-flex justify-content-start ml-3">
      <div class="form-group me-2">
        <input type="text" class="form-control" placeholder="Search string" v-model="search_string">
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>
  </div>

  <form @submit.prevent="addUser" class="mb-3">
    <div class="form-group mb-1">
      <input type="text" class="form-control" placeholder="Name" v-model="user.name">
    </div>
    <div class="form-group mb-1">
      <input type="email" class="form-control" placeholder="Email" v-model="user.email">
    </div>
    <div class="form-group mb-1">
      <input type="password" class="form-control" placeholder="Password" v-model="user.password">
    </div>
    <div class="form-group mb-3">
      <input type="password" class="form-control" placeholder="Password confirmation" v-model="user.password_confirmation">
    </div>
    <button type="submit" class="btn btn-outline-success btn-block">Save</button>
  </form>

  <nav aria-label="page-navigation">
    <ul class="pagination">
      <li v-bind:class="[{ disabled: !pagination.prev_page_url}]" class="page-item"><a class="page-link" href="#" @click="fetchUsers(pagination.prev_page_url)">Previous</a></li>
      <li class="page-item disabled"><a class="page-link text-dark" href="#">Page {{ pagination.current_page }} of {{ pagination.last_page }}</a></li>
      <li v-bind:class="[{ disabled: !pagination.next_page_url}]" class="page-item"><a class="page-link" href="#" @click="fetchUsers(pagination.next_page_url)">Next</a></li>
    </ul>
  </nav>

  <div class="card card-body mb-2" v-for="user in users" v-bind:key="user.id">
    <h3>{{ user.name }}</h3>
    <span>{{ user.email }}</span>
    <hr>
    <div class="d-flex justify-content-between">
      <button @click="editUser(user)" class="btn btn-outline-warning btn-sm col-2">Edit</button>
      <button @click="getPayments(user.id)" class="btn btn-outline-info btn-sm col-2">Payments</button>
      <button @click="deleteUser(user.id)" class="btn btn-outline-danger btn-sm col-2">Delete</button>
    </div>
  </div>

</template>

<script>
export default {
  data() {
    return {
      currentUser: {},
      token: localStorage.getItem('token'),
      formData: {

      },
      errors: {},
      users: [],
      user: {
        id: '',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      user_id: '',
      pagination: {},
      edit: false,
      search_id: '',
      search_string: ''
    }
  },
  mounted(){
    fetch('api/user',{ headers:{'Authorization':`Bearer ${this.token}`,'Accept':'application/json','Content-Type':'application/json'}}).then(
        response => response.json()).then( response => {
      this.currentUser.name = response.name ?? null;
      this.currentUser.email = response.email ?? null;
      if (!this.currentUser.name) { this.$router.push('/') }
    }).catch(err=>console.log(err))
  },
  created() {
    this.fetchUsers();
  },
  methods: {
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
    fetchUsers(page_url){
      page_url = page_url || 'api/users';
      fetch(page_url, {
        headers: { 'Authorization':`Bearer ${this.token}`, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN').slice(0,-2) }
      }).then(res => res.json()).then(res => {
        this.users = res.data
        if (res.meta && res.links){
          this.makePagination(res.meta, res.links);
        }
      }).catch(err => console.log(err));
    },
    makePagination(meta, links){
      this.pagination = {
        current_page: meta.current_page,
        last_page: meta.last_page,
        next_page_url: links.next,
        prev_page_url: links.prev,
      };
    },
    deleteUser(id){
      if(confirm('Are You Sure?')){
        fetch(`api/users/${id}`, {
          method: 'delete',
          headers: { 'Authorization':`Bearer ${this.token}`, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN').slice(0,-2) }
        }).then(res => res.json()).then(res => {
          alert('User is deleted'); this.fetchUsers();
        }).catch(err => console.log(err));
      }
    },
    addUser(){
      if(this.edit === false) {
        fetch('api/users', {
          method: 'post',
          headers: { 'Authorization':`Bearer ${this.token}`, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN').slice(0,-2) },
          body: JSON.stringify(this.user)
        }).then(res => res.json()).then(res => {
          this.user = { name:'', email:'', password:'', password_confirmation:'' };
          this.fetchUsers();
        }).catch(err => console.log(err));
      } else {
        fetch(`api/users/${this.user.id}`, {
          method: 'put',
          headers: { 'Authorization':`Bearer ${this.token}`, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN').slice(0,-2) },
          body: JSON.stringify({
            name: this.user.name,
            email: this.user.email,
            password: this.user.password,
            password_confirmation: this.user.password_confirmation,
          })
        }).then(res => res.json()).then(res => {
          this.user = { name:'', email:'', password:'', password_confirmation:'' };
          this.fetchUsers();
        }).catch(err => console.log(err));
      }
    },
    editUser(user){
      this.edit = true;
      this.user.id = user.id;
      this.user.name = user.name;
      this.user.email = user.email;
      this.user.password = user.password;
      this.user.password_confirmation = user.password_confirmation;
    },
    getPayments(id){
      localStorage.setItem('paymentsUserId',id);
      this.$router.push('/payments')
    },
    searchById(){
      if (!this.search_id) { return; }
      this.fetchUsers(`api/users/${this.search_id}`);
    },
    searchByString(){
      if (!this.search_string) { return; }
      this.fetchUsers(`api/users/search/${this.search_string}`);
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
};
</script>
