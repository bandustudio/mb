var cache = {}
const Splash = {
  template: '#splash',
  created: function() {
    if(!cache.slides){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/vehicles', {}, {emulateJSON:true}).then(function(res){
        this.slides = res.data.data
        cache.slides = this.slides
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
      $('.slick').slick({
        dots: true,
        autoplay: true,
        autoplaySpeed: 3000,
        infinite: true,
        speed: 1500,
        fade: true,
        cssEase: 'linear'        
      })
    },500)
  },
  data: function() {
    return{
      slides:cache.slides||{},
      posts:cache.posts||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }  
};

const Resource = {
  template: '#resource',
  mounted : function(){
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app/resource', {payload:location.pathname}, {emulateJSON:true}).then(function(res){
      this.item = res.data.data
      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })    
  },
  data: function() {
    return{
      item: {data:{}},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Items = {
  template: '#item',
  mounted: function() {
    var getCandidate = this.getCandidate()

    if(this.item && this.item.complete){
      helper.clear('item')
      this.item = helper.champ().item
    }    

    helper.collect('item')

    $('input[type="hidden"]').trigger("change")
    $('.item input[name="full_name"]').on('change keyup',function(){
      var val = $(this).val();
      if(val){
        $('.item input[name="first_name"]').val(val.split(' ').slice(0, -1).join(' ')).trigger('change');
        $('.item input[name="last_name"]').val(val.split(' ').slice(-1).join(' ')).trigger('change');
      }
    });
  },    
  methods: {
    showImage : function(data){
      return data.picture || "nose"
    },
    getCandidate: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/candidato', {}, {emulateJSON:true}).then(function(res){
        candidato = res.data
        this.candidato = candidato
        $('input[name="gestor_id"]').val(res.data.id).trigger('change')
      }, function(error){
        console.log(error.statusText)
      })
    }
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

const Beneficios = {
  template: '#beneficios',
  data: function() {
    return{
      msg: 'This is Users page',
      settings: helper.getAttributes($('html'))
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
    {path: '/opener', component: Opener, meta : { title: 'Redirigiendo...'}},
    {path: '/vehicles', component: Items,  meta : { title: 'Vehículos'}},
    {path: '/contacto', component: Contacto, meta : { title: 'Contacto'}},
    {path: '/terminos', component: Terminos, meta : { title: 'Términos y condiciones'}},
    {path: '/atencion', component: Atencion, meta : { title: 'Atención'}},
    {path: "*", component: Resource, meta : { title: ''}}
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
    $('.navbar-menu, navbar-burger').removeClass('is-active')
  }, 1)
})

const app = new Vue({ router: router,
  created: function () {
    $('.loading, .hidden-loading').removeClass('loading hidden-loading')
  }}).$mount('#app');