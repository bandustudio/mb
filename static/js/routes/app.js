const Splash = {
  template: '#splash',
  mounted: function(){
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
        //$('.slick-pane').css({'max-height':($(window).height()-$('.navbar').height())+'px'})
      },100);
    }, function(error){
      console.log(error.statusText)
    })
  },
  data: function() {
    return{
      products:{},
      posts:{},
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

      console.log(this.dealers)
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
  mounted : function(){
    if(cache.layout && cache.layout.services){
      this.data = cache.layout.services[this.$route.params.slug]||{}
    } else {
      helper.is_loading()
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app'+location.pathname, {}, {emulateJSON:true}).then(function(res){
        this.data = res.data.data
        helper.is_loaded()
      }, function(error){
        console.log(error.statusText)
      })  
    }    
  },
  data: function() {
    return{
      data: {},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Products = {
  template: '#products',
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

const Product = {
  template: '#product',
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
      if($('.submitable').hasClass('disabled')===false){
        helper.send('lead',{},function(){
          helper.clear('lead')
          if($('.section .notification').is(':hidden')) {
            $('.section .lead').slideUp(200,function(){
              $('.section .notification').slideDown()
            })          
          }
        })
      }
    },
    showLead: function(){
      if($('.section .notification').is(':visible')) {
        $('.section .notification').hide()
      }      
      if($('.lead').is(':hidden')){
        $('.lead').find('input,select,textarea').each(function(){
          $(this).val('')
        })

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
    {path: '/products', component: Products,  meta : { title: 'Vehículos'}},
    {path: '/products/:cat', component: Products,  meta : { title: ''}},
    {path: '/dealers', component: Dealers,  meta : { title: 'Sucursales'}},
    {path: '/dealers/:slug', component: Dealer,  meta : { title: 'Dealer'}},
    {path: '/services', component: Services,  meta : { title: 'Sucursales'}},
    {path: '/services/:slug', component: Service,  meta : { title: 'Dealer'}},
    {path: '/contact', component: Contacto, meta : { title: 'Contacto'}},
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
    $('.is-cat').slideUp('fast')
  }, 1)
})

const app = new Vue({ router: router,
  created: function () {
    $.post(helper.getAttributes($('html')).endpoint + '/app/subnav',function(res){
      cache.layout = res
      $('.subnav').html($.templates('#subnav').render(res))  
      $('.navbar-end').prepend($.templates('#navitems').render(res))  

      $('.is-cat-link').hover(function(){
        var visible = $('.is-cat').is(':visible')
        $('.is-cat').css({display:'none'})
        if(location.pathname!=$(this).attr('href')){
          $('.is-cat-' + $(this).attr('cat')).fadeIn('fast')
        }
      },function(){
      });

      $('.is-cat').hover(function(){
      },function(){
        $(this).slideUp('fast')
      });  

      $('.is-cat-services .item-pic').hover(function(){
        $(this).css('background-image','url('+$(this).attr('pic-on')+')')
      },function(){
        $(this).css('background-image','url('+$(this).attr('pic-off')+')')
      })

    })      
    $('.hidden-loading').removeClass('hidden-loading')
  }/*,
  mounted : function(){
    window.addEventListener('click', event => {
      const { target } = event
      console.log("a")
      console.log(target)
      // handle only links that do not reference external resources
      if (target && target.matches("a:not([href*='://'])") && target.href) {
        console.log("b")
        // some sanity checks taken from vue-router:
        // https://github.com/vuejs/vue-router/blob/dev/src/components/link.js#L106
        const { altKey, ctrlKey, metaKey, shiftKey, button, defaultPrevented } = event
        // don't handle with control keys
        if (metaKey || altKey || ctrlKey || shiftKey) return
        // don't handle when preventDefault called
        if (defaultPrevented) return
        // don't handle right clicks
        if (button !== undefined && button !== 0) return
        // don't handle if `target="_blank"`
      console.log("2")
        if (target && target.getAttribute) {
          const linkTarget = target.getAttribute('target')
          if (/\b_blank\b/i.test(linkTarget)) return
        }
        // don't handle same page links/anchors
        const url = new URL(target.href)
        const to = url.pathname
        console.log("1")
        if (window.location.pathname !== to && event.preventDefault) {
          console.log("link")
          event.preventDefault()
          this.$router.push(to)
        }
      }
    })    
  }*/
}).$mount('#app');