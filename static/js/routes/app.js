var cache = {}

const Splash = {
  template: '#splash',
  created: function() {
    if(!cache.vehicles){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/vehicles', {}, {emulateJSON:true}).then(function(res){
        this.vehicles = res.data.data
        cache.vehicles = this.vehicles
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
      vehicles:cache.vehicles||{},
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
    helper.is_loading()
    $('.section, #map').css({'height':($(window).height()-$('.navbar').height() - 100)+'px'})
    this.title = this.$route.params.slug||"Nuestras sucursales"
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      var features = res.data.data
      this.features = features

      mapboxgl.accessToken = helper.mapbox.accessToken
      this.map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/dark-v9'
      });        


      //L.mapbox.accessToken = geo.mapbox.accessToken
      //map = L.mapbox.map('map', 'mapbox.streets');
      //map.setCenter([-58.46762990000002,-34.5488008])
      //map.setZoom(13)

      var bounds = new mapboxgl.LngLatBounds();

      for(var i in features){
        var p = features[i]
        if(p.lat && p.lng){
          var marker = new mapboxgl.Marker()
            .setLngLat([p.lng,p.lat])
            .addTo(this.map);
          bounds.extend([p.lng,p.lat]);

          //L.marker([locs[i].lat,locs[i].lng], {icon:geo.icon({id:locs[i].id,displayName:locs[i].title + ' ('+locs[i].region_id+')',className:'me',colorId:locs[i].region_id})}).addTo(map);
        }
      }

      this.map.fitBounds(bounds, { padding: 50 })
      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })    
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
      features:{},
      title:'',
      map:{},
      helper : helper,
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
      helper.send('lead','{"redirect":"/consulta-exito"}')
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
  template: '#atencion',
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
    {path: '/posts', component: Posts,  meta : { title: 'Artículos'}},
    {path: '/posts/:slug', component: Post,  meta : { title: 'Artículo'}},
    {path: '/vehicles', component: Items,  meta : { title: 'Vehículos'}},
    {path: '/vehicles/:cat', component: Items,  meta : { title: ''}},
    {path: '/dealers', component: Dealers,  meta : { title: 'Sucursales'}},
    {path: '/consulta-exito', component: ConsultaExito,  meta : { title: ''}},
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
    $('.is-cat').slideUp('fast')
  }, 1)
})

const app = new Vue({ router: router,
  created: function () {
    $('.hidden-loading').removeClass('hidden-loading')
  }}).$mount('#app');