var cache = {}

const Splash = {
  template: '#splash',
  created: function() {
    if(!cache.products){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/products', {}, {emulateJSON:true}).then(function(res){
        this.products = res.data.data
        cache.products = this.products
      }, function(error){
        console.log(error.statusText)
      })
    }
    if(!cache.posts){
      cache.creating = 1
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/posts', {}, {emulateJSON:true}).then(function(res){
        this.posts = res.data.data
        cache.posts = this.posts
        setTimeout(function(){
          $('.slick').on('init', function(event, slick, currentSlide, nextSlide){
            cache.creating = 0
          });
          $('.slick').slick({
            dots: true,
            arrows:true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            accessibility: true
          }).addClass('fadeIn')
          $('.slick-pane').css({'min-height':($(window).height()-$('.navbar').height())+'px'})
        },100);
      }, function(error){
        console.log(error.statusText)
      })
    }
  },
  mounted: function(){
    if(!cache.creating){
      setTimeout(function(){
        $('.slick').slick({
          dots: true,
          arrows:true,
          infinite: true,
          speed: 500,
          fade: true,
          cssEase: 'linear'
        }).addClass('fadeIn')
        $('.slick-pane').css({'min-height':($(window).height()-$('.navbar').height())+'px'})
      },100)
    }
  },
  data: function() {
    return{
      products:cache.products||{},
      posts:cache.posts||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }  
};

const markerContent = Vue.extend({
  template: '<div class="marker-content"></div>',
  computed: {
    someVuexData() {
      return this.$store.getters.someVuexData;
    }
  }
});

const Dealers = {
  template: '#dealers',
  mounted: function() {
    $('.section, #map').css({'height':($(window).height()-$('.navbar').height() - 100)+'px'})
    this.title = this.$route.params.slug||"Nuestras sucursales"
    this.dealers = layout.dealers

    mapboxgl.accessToken = helper.mapbox.accessToken
    this.map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/dark-v9'
    });        

    var bounds = new mapboxgl.LngLatBounds();

    for(var i in this.dealers){
      var p = this.dealers[i]
      if(p.lat && p.lng){
        var popup = new mapboxgl.Popup()
          .setHTML($.templates('#map_info').render(p))

        var marker = new mapboxgl.Marker()
          .setLngLat([p.lng,p.lat])
          .setPopup(popup)
          .addTo(this.map);

        bounds.extend([p.lng,p.lat]);
      }
    }

    this.map.fitBounds(bounds, { padding: 50 })
  },
  methods: {
    flyTo(feature){
      this.map.flyTo({
        center: [
          feature.lng,
          feature.lat
        ],
        zoom: 17
      });
    }
  },
  data: function() {
    return{
      helper : helper,
      dealers: {},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Dealer = {
  template: '#dealer',
  mounted : function(){
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.data = res.data.data
      helper.is_loaded()
    }, function(error){
      helper.is_loaded()
      $('.section').html($.templates('#notfound').render());
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

const Items = {
  template: '#items',
  mounted: function() {
    helper.is_loading()
    this.title = this.$route.params.cat||"Nuestros vehículos"
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.items = res.data.data
      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })    
  },    
  data: function() {
    return{
      items:{},
      title:'',
      helper : helper,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Item = {
  template: '#item',
  mounted : function(){
    helper.collect('lead');
    helper.is_loading()
    this.dealers = layout.dealers
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.data = res.data.data
      helper.is_loaded()
    }, function(error){
      helper.is_loaded()
      $('.section').html($.templates('#notfound').render());
      console.log(error.statusText)
    })    
  },
  methods: {
    consultar: function(){
      helper.send('lead',{},function(){
        console.log("1")
        if($('.section .notification').is(':hidden')) {
            console.log("2")
          $('.section .lead').slideUp(200,function(){
            $('.section .notification').slideDown()    
          })          
        }
      })
    },
    showLead: function(){
      if($('.lead').is(':hidden')){
        $('.lead').slideDown()
        setTimeout(function() {
          window.scrollTo(0,$('.lead').offset().top + 10);
          $('input[name="full_name"]').focus()
        }, 300);        
      }
    }
  },
  data: function() {
    return{
      lead:{},
      data: {data:{}},
      settings: helper.getAttributes($('html')),
      hash : location.hash.replace('#','')
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
      $('.section').html($.templates('#notfound').render());
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
  template: '#call',
  data: function() {
    return{
      msg: 'This is Users page',
      settings: helper.getAttributes($('html'))
    }
  }
}

const ConsultaExito = {
  template: '#consulta_exito',
  data: function() {
    return{
      settings: helper.getAttributes($('html'))
    }
  }
}

const Contacto = {
  template: '#contact',
  mounted: function() {
    helper.collect('contact');
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
            title:"Felicitaciones " + helper.champ().contact.first_name,
            text:"Nuestro asesor se pondrá en contacto con vos para ayudarte a ahorrar."
          })
          helper.send('contact', {redirect:"/"})
        },1000)   
      }
    }
  },  
  data: function() {
    return{
      item : helper.champ().item||{},
      contact : helper.champ().contact||{},
      settings : helper.getAttributes($('html')),
      hash : location.hash.replace('#','')
    }
  }
}

const Terminos = {
  template: '#tos',
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
    {path: '/posts', component: Posts,  meta : { title: 'Artículos'}},
    {path: '/posts/:slug', component: Post,  meta : { title: 'Artículo'}},
    {path: '/products', component: Items,  meta : { title: 'Vehículos'}},
    {path: '/products/:cat', component: Items,  meta : { title: ''}},
    {path: '/dealers', component: Dealers,  meta : { title: 'Sucursales'}},
    {path: '/dealers/:slug', component: Dealer,  meta : { title: 'Dealer'}},
    {path: '/contact', component: Contacto, meta : { title: 'Contacto'}},
    {path: '/tos', component: Terminos, meta : { title: 'Términos y condiciones'}},
    {path: '/call', component: Atencion, meta : { title: 'Atención'}},
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
    $('.is-cat').slideUp('fast')
  }, 1)
})

const app = new Vue({ router: router,
  created: function () {
    $('.hidden-loading').removeClass('hidden-loading')
  }}).$mount('#app');