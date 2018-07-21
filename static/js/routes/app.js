var cache = {}

const Splash = {
  template: '#splash',
  created: function() {
    if(!cache.items){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/items', {}, {emulateJSON:true}).then(function(res){
        this.items = res.data.data
        cache.items = this.items
      }, function(error){
        console.log(error.statusText)
      })
    }
    if(!cache.posts){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/posts', {}, {emulateJSON:true}).then(function(res){
        this.posts = res.data.data
        cache.posts = this.posts
      }, function(error){
        console.log(error.statusText)
      })
    }
  },
  mounted: function(){
    setTimeout(function(){
      $('.slides').slick({
        dots: true,
        arrows:true,
        infinite: true,
        speed: 500,
        fade: true,
        cssEase: 'linear'
      })
      $('.slick-pane').height(($(window).height()-110)+'px')
    },1000)
  },
  data: function() {
    return{
      items:cache.items||{},
      posts:cache.posts||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }  
};

const Items = {
  template: '#item',
  mounted: function() {
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.items = res.data.data
      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })    
  },    
  data: function() {
    return{
      candidato: candidato,
      item: helper.champ().item||{},
      helper : helper,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Item = {
  template: '#item',
  created: function() {
    //helper.progress_bar('')
  },
  mounted : function(){
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.data = res.data.data
      helper.is_loaded()
    }, function(error){
      helper.is_loaded()
      $('.content').html($.templates('#notfound').render());
      console.log(error.statusText)
    })    
  },
  data: function() {
    return{
      data: {data:{}},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Posts = {
  template: '#posts',
  mounted: function() {
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.data = res.data.data
      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })    
  },    
  data: function() {
    return{
      data:{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Post = {
  template: '#post',
  mounted : function(){
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.data = res.data.data
      helper.is_loaded()
    }, function(error){
      helper.is_loaded()
      $('.content').html($.templates('#notfound').render());
      console.log(error.statusText)
    })    
  },
  data: function() {
    return{
      data: {data:{}},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Atencion = {
  template: '#atencion',
  data: function() {
    return{
      msg: 'This is Users page',
      settings: helper.getAttributes($('html'))
    }
  }
}

const Contacto = {
  template: '#contacto',
  mounted: function() {
    helper.collect('contacto');
    //helper.send('item')
  },
  methods : {
    enviar : function(){
      console.log("enviar Contacto!");
      var button = $('.item .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')  
        setTimeout(function(){
          helper.setFlash({
            title:"Felicitaciones " + helper.champ().contacto.first_name,
            text:"Nuestro asesor se pondrá en contacto con vos para ayudarte a ahorrar."
          })
          helper.send('contacto', '{"redirect":"/"}')
        },1000)   
      }
    }
  },  
  data: function() {
    return{
      item : helper.champ().item||{},
      contacto : helper.champ().contacto||{},
      settings : helper.getAttributes($('html')),
      hash : location.hash.replace('#','')
    }
  }
}

const Terminos = {
  template: '#terminos',
  data: function() {
    return{
      msg: 'This is Users page',
      settings: helper.getAttributes($('html'))
    }
  }
}

const Opener = {
  template: '#opener',
  mounted: function() {
    localStorage.setItem("token", this.$route.query.token);
    var redirect = localStorage.getItem("redirect")
    , redirect_url = !redirect || redirect == '/' ? this.$route.query.url : redirect;
    $('.redirect--url').text(redirect_url + ' ...');
    localStorage.removeItem("redirect");
    setTimeout(function(){
      location.href = redirect_url;
    },200);
  },  
  data: function() {
    return{
      url: this.$route.query.url,
      settings: helper.getAttributes($('html'))
    }
  }
}

const PageNotFound = {
  template: '#pagenotfound',
  data: function() {
    return{
      msg: 'This is Users page',
      settings: helper.getAttributes($('html'))
    }
  }
}

const router = new VueRouter({
  mode: 'history',
  routes: [
    {path: '/', component: Splash, meta : { title: 'Mercedes-Benz'}},
    {path: '/items', component: Items,  meta : { title: 'Vehículos'}},
    {path: '/posts', component: Posts,  meta : { title: 'Artículos'}},
    {path: '/posts/:slug', component: Post,  meta : { title: 'Artículo'}},
    {path: '/contacto', component: Contacto, meta : { title: 'Contacto'}},
    {path: '/terminos', component: Terminos, meta : { title: 'Términos y condiciones'}},
    {path: '/atencion', component: Atencion, meta : { title: 'Atención'}},
    {path: '/opener', component: Opener, meta : { title: 'Redirigiendo...'}},
    {path: "*", component: Item, meta : { title: ''}}
  ]
});

router.beforeEach(function (to, from, next) { 
  document.title = to.meta.title;
  setTimeout(function() {
    window.scrollTo(0, 0);
  }, 100);
  next();
});

router.afterEach(function (to, from, next) {
  setTimeout(function() {
    $('.navbar-menu, .navbar-burger').removeClass('is-active')
  }, 1)
})

const app = new Vue({ router: router,
  created: function () {
    $('.loading, .hidden-loading').removeClass('loading hidden-loading')
  }}).$mount('#app');