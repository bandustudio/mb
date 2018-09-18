const Splash = {
  template: '#splash',
  mounted: function(){
    helper.getFlash()
    if(cache.layout && cache.layout.services){
      this.services = cache.layout.services
    } else {
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/services', {}, {emulateJSON:true}).then(function(res){
        this.services = res.data.data
      }, function(error){
        console.log(error.statusText)
      })
    }

    this.$http.post(helper.getAttributes($('html')).endpoint + '/app/posts', {}, {emulateJSON:true}).then(function(res){
      var data = res.data.data
      var posts = []
      data.forEach(function(post){
        if(!posts[post.position]) posts[post.position] = []
        posts[post.position].push(post)
      })

      this.posts = posts
      setTimeout(function(){
        $('.slick').slick({
          dots: true,
          arrows:true,
          infinite: true,
          speed: 500,
          fade: true,
          cssEase: 'linear',
          accessibility: true,
          adaptiveHeight: true
        }).addClass('fadeIn')
        $('.services .item-pic').hover(function(){
          $(this).css('background-image','url('+$(this).attr('pic-on')+')')
        },function(){
          $(this).css('background-image','url('+$(this).attr('pic-off')+')')
        })
        //$('.slick-pane').css({'max-height':($(window).height()-$('.navbar').height())+'px'})
      },100);
    }, function(error){
      console.log(error.statusText)
    })
  },
  data: function() {
    return{
      posts:{},
      services:{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }  
};

const Dealers = {
  template: '#dealers',
  mounted: function(){
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app/dealers', {}, {emulateJSON:true}).then(function(res){
      this.dealers = res.data.data
      this.title = this.$route.params.slug||"Nuestras sucursales"
      this.initMap()
    })
  },
  methods: {
    initMap : function(){
      $('.section, #map').css({'height':($(window).height()-$('.navbar').height() - 100)+'px'})

      mapboxgl.accessToken = helper.mapbox.accessToken
      this.map = new mapboxgl.Map({
        container: 'map',
        style: helper.mapbox.style 
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
      this.map.resize()
    },
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
      document.title = this.data.title
      mapboxgl.accessToken = helper.mapbox.accessToken
      this.map = new mapboxgl.Map({
        container: 'dealer_map',
        style: helper.mapbox.style 
      });   

      var marker = new mapboxgl.Marker()
        .setLngLat([this.data.lng,this.data.lat])
        .addTo(this.map);

      this.map.setCenter([this.data.lng,this.data.lat])
      this.map.setZoom(16)
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

const Services = {
  template: '#services',
  mounted: function() {
    if(cache.layout && cache.layout.services){
      this.services = cache.layout.services
    } else {
      helper.is_loading()
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
        this.services = res.data.data
        helper.is_loaded()
      }, function(error){
        console.log(error.statusText)
      })  
    }
    setTimeout(function(){
      $('.services .item-pic').hover(function(){
        $(this).css('background-image','url('+$(this).attr('pic-on')+')')
      },function(){
        $(this).css('background-image','url('+$(this).attr('pic-off')+')')
      })
    },200)        
  },
  data: function() {
    return{
      helper : helper,
      services: {},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Service = {
  template: '#service',
  name:'service',
  mounted : function(){
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.item = res.data.item.data
      this.dealers = res.data.dealers.data
      document.title = res.data.item.title
      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })  
  },
  methods: {
    sendLead: function(){
      if(!this.loading){
        this.loading = true
        var lead = 
        helper.send('lead',{redirect:"/datosrecibidos",dump:true},function(res){
          if(res.status=='success'){
            helper.setFlash({
              title:"¡Recibimos tu solicitud!",
              text:'Un asesor se va a contactar con vos por una de las vías que nos sugeriste.'
            })
          }
        })
      }
    }
  },
  data: function() {
    return{
      loading:false,
      item: {data:{}},
      dealers: {data:{}},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Products = {
  template: '#products',
  mounted: function() {
    helper.is_loading()
    this.title = this.$route.params.slug||"Nuestros vehículos"
    var that = this
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.items = res.data.data
      setTimeout(function(){
        that.slick()
      },100);

      helper.is_loaded()
    }, function(error){
      console.log(error.statusText)
    })    
  },    
  methods: {
    slick : function(){
      $('.slick').slick({
        centerMode: true,
        centerPadding: '60px',
        slidesToShow: 3,
        adaptiveHeight: true,
        responsive: [
          {
            breakpoint: 1024,
            settings: {
              arrows: false,
              centerMode: true,
              centerPadding: '40px',
              slidesToShow: 1
            }
          },
          {
            breakpoint: 480,
            settings: {
              arrows: false,
              centerMode: true,
              centerPadding: '40px',
              slidesToShow: 1
            }
          }
        ]
      }).removeClass('loading').addClass('fadeIn')
      //$('.slick-pane').css({'height':($(window).height()-$('.navbar').height()-50)+'px'})
      $('input[name="filter"]').focus()
    },
    filterSlick: function(){

      $('.slick').slick('unslick');
      $('.slick').addClass('loading').html('')
      
      var value = $('input[name="filter"]').val().trim()
      
      this.items.forEach(function(item){

        if(item.title.indexOf(value) > -1 || 
          item.intro.indexOf(value) > -1){
          $('.slick').append($.templates('#slick_item').render(item))
        }
      })

      this.slick()
    }
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

const Product = {
  template: '#product',
  name: 'product',
  mounted : function(){
    helper.is_loading()
    this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
      this.data = res.data
      document.title = this.data.item.data.title
      helper.is_loaded()
    }, function(error){
      helper.is_loaded()
      $('.section').html($.templates('#notfound').render());
      console.log(error.statusText)
    })    
  },
  methods: {
    showLead: function(){
      $('.modal-button').first().click()
    },
    sendLead: function(){
      if(!this.loading){
        this.loading = true
        var lead = 
        helper.send('lead',{redirect:"/datosrecibidos",dump:true},function(res){
          if(res.status=='success'){
            helper.setFlash({
              title:"¡Recibimos tu solicitud!",
              text:'Un asesor se va a contactar con vos por una de las vías que nos sugeriste.'
            })
          }
        })
      }
    }
  },
  data: function() {
    return{
      loading:false,
      filters : helper.filters,
      data: {item:{data:{}},models:{}},
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
      document.title = this.data.title
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

const DatosRecibidos = {
  template: '#datosrecibidos',
  name:'datosrecibidos',
  mounted: function(){
    helper.getFlash()
  },
  data: function() {
    return{
      settings: helper.getAttributes($('html'))
    }
  }  
}

const Turnos = {
  template: '#turnos',
  name:'turnos',
  mounted: function() {
    helper.capture('turnos')
    $('#scheduled').scroller({ 
      preset: 'datetime', 
      dateFormat: "dd-mm-yy", 
      timeFormat: 'H:ii', 
      ampm: false, 
      dateOrder: "ddmmyy" 
    });    
  },
  methods : {
    solicitarTurno : function(){
      if(!this.loading){
        this.loading = true
        var turnos = helper.champ().turnos
        helper.setFlash({
          title:"Felicitaciones " + turnos.full_name,
          text:"Un asesor se pondrá en contacto con vos."
        })
        setTimeout(function(){
          helper.send('turnos', {redirect:"/datosrecibidos",dump:true})
        },1000)   
      }
    }
  },  
  data: function() {
    return{
      loading: false,
      item : helper.champ().item||{},
      turnos : helper.champ().turnos||{},
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
    {path: '/products', component: Products,  meta : { title: 'Vehículos'}},
    {path: '/products/:slug', component: Products,  meta : { title: ''}},
    {path: '/dealers', component: Dealers,  meta : { title: 'Sucursales'}},
    {path: '/dealers/:slug', component: Dealer,  meta : { title: 'Dealer'}},
    {path: '/services', component: Services,  meta : { title: 'Servicios'}},
    {path: '/services/:slug', component: Service,  meta : { title: 'Servicio'}},
    {path: '/turnos', component: Turnos, meta : { title: 'Turno online'}},
    {path: '/datosrecibidos', component: DatosRecibidos, meta : { title: 'Datos recibidos'}},
    {path: '/tos', component: Terminos, meta : { title: 'Términos y condiciones'}},
    {path: '/call', component: Atencion, meta : { title: 'Atención'}},
    {path: '/opener', component: Opener, meta : { title: 'Redirigiendo...'}},
    {path: "*", component: Product, meta : { title: ''}}
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
    $('.is-cat').slideUp(250)
  }, 1)
})

var delay=700, setTimeoutConst;

const app = new Vue({ router: router,
  created: function () {
    $.post(helper.getAttributes($('html')).endpoint + '/app/subnav',function(res){
      cache.layout = res
      
      $('.subnav').html($.templates('#subnav').render(res))  
      $('.navbar-end').append($.templates('#navitems').render(res))  

      $('.is-cat-link').hover(function(){
        var self = this
        setTimeoutConst = setTimeout(function(){
          $('.is-cat').css({display:'none'})
          if(location.pathname!=$(self).attr('href')){
            $('.is-cat-' + $(self).attr('cat')).addClass('fadeIn').fadeIn(350)
          }
        }, delay);

      },function(){
        clearTimeout(setTimeoutConst );
      });

      $('.is-cat').hover(function(){
      },function(){
        $(this).removeClass('fadeIn').slideUp(250)
      });  

      $('.services .item-pic').hover(function(){
        $(this).css('background-image','url('+$(this).attr('pic-on')+')')
      },function(){
        $(this).css('background-image','url('+$(this).attr('pic-off')+')')
      })
    })      
    $('.hidden-loading').removeClass('hidden-loading')
  }
}).$mount('#app');