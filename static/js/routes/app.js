var candidato = {};

const Splash = {
  template: '#splash',
  mounted: function() {
    helper.getFlash();
    $('[data-typer-targets]').typer();
    $.typer.options.textColor = 'black';
  },
  data: function() {
    return{
      item: helper.champ().item||{},
      settings: helper.getAttributes($('html'))
    }
  }  
};

const Item = {
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
    {path: '/', component: Splash, meta : { title: 'MercedesBenz'}},
    {path: '/opener', component: Opener, meta : { title: 'Redirigiendo...'}},
    {path: '/item', component: Item,  meta : { title: 'Item'}},
    {path: '/contacto', component: Contacto, meta : { title: 'Contacto'}},
    {path: '/terminos', component: Terminos, meta : { title: 'Términos y condiciones'}},
    {path: '/atencion', component: Atencion, meta : { title: 'Atención'}},
    {path: "*", component: PageNotFound, meta : { title: 'Not found'}}
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