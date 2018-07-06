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
      cotizacion: helper.champ().cotizacion||{},
      settings: helper.getAttributes($('html'))
    }
  }  
};

const Cotizacion = {
  template: '#cotizacion',
  mounted: function() {
    var getCandidate = this.getCandidate()

    if(this.cotizacion && this.cotizacion.complete){
      helper.clear('cotizacion')
      this.cotizacion = helper.champ().cotizacion
    }    

    helper.collect('cotizacion')

    $('input[type="hidden"]').trigger("change")
    $('.cotizacion input[name="full_name"]').on('change keyup',function(){
      var val = $(this).val();
      if(val){
        $('.cotizacion input[name="first_name"]').val(val.split(' ').slice(0, -1).join(' ')).trigger('change');
        $('.cotizacion input[name="last_name"]').val(val.split(' ').slice(-1).join(' ')).trigger('change');
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
      cotizacion: helper.champ().cotizacion||{},
      helper : helper,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cotizacion2 = {
  template: '#cotizacion2',
  mounted: function() {
    this.getVersiones;
    
    helper.collect('cotizacion')
    helper.send('cotizacion','{"dump":false}')

    if(this.cotizacion.brand_id){
      this.brandChange()
    } else {
      $('select[name="mt_year"]').focus()  
    }

  },
  methods: {
    brandChange: function(){
      $('select[name="version_id"]').parent().addClass('is-loading')
      this.getVersiones()
    },
    getVersiones: function(e){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/versiones', {marca:this.cotizacion.brand_id}, {emulateJSON:true}).then(function(res){
        this.versiones = res.data.data
        $('select[name="version_id"]').parent().removeClass('is-loading')
        $('select[name="version_id"]').focus()
      }, function(error){
        console.log(error.statusText)
      })
    }
  },
  data: function() {
    return{
      versiones : null,
      candidato: candidato,
      cotizacion : helper.champ().cotizacion||{},
      helper : helper,
      token : helper.champ('token')||{},      
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cotizacion3 = {
  template: '#cotizacion3',
  mounted: function() {
    helper.collect('cotizacion');
    helper.initAutocomplete('address','cotizacion');
  },    
  methods : {
    cotizarAhora : function(){
      if(!$('input[name="accept_terms"]').is(':checked')){
        return swal({   
          title: "Atención",
          text: '<h4>Es necesario que leas y aceptes nuestras bases y condiciones para continuar.</h4><p><a href="' + helper.getAttributes($('html')).app + '/terminos" target="_blank">Leer Términos y condiciones</a></p><div class="inline-background accept-bases-arrow"></div>',
          html:true,
          type: "warning"
        })
      }      
      var button = $('.cotizacion .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')  
        setTimeout(function(){      
          helper.setFlash({
            title:"Recibimos tu consulta. Felicitaciones y bienvenido " + helper.champ().cotizacion.first_name,
            text:'Nuestro asesor se pondrá en contacto con vos para ayudarte a ahorrar.<br>Podés seguir la evolución de tu consulta accediendo a nuestra <a href="' + helper.getAttributes($('html')).clientes + '">portal de clientes y empresas</a> con los datos que te enviamos al mismo email que nos proporcionaste. <br><br>Si todavía no lo hiciste lee nuestros <a href="/terminos">términos y condiciones</a>. <br><br><em>Atte. el equipo de MercedesBenz.com'
          });
          helper.setValue('cotizacion','complete',1)
          helper.send('cotizacion','{"redirect":"/cotizacion/exito","dump":false}');
        },1000)   
      }
    }
  },
  data: function() {
    return{
      candidato: candidato,
      cotizacion : helper.champ().cotizacion||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const CotizacionExito = {
  template: '#cotizacionexito',
  data: function() {
    return{
      candidato: candidato,
      cotizacion : helper.champ().cotizacion||{},
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
    //helper.send('cotizacion')
  },
  methods : {
    enviar : function(){
      console.log("enviar Contacto!");
      var button = $('.cotizacion .button.rounded-button-grey')
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
      cotizacion : helper.champ().cotizacion||{},
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
    {path: '/cotizacion', component: Cotizacion,  meta : { title: 'Cotización 1/3'}},
    {path: '/cotizacion/2', component: Cotizacion2, meta : { title: 'Cotización 1/3'}},
    {path: '/cotizacion/3', component: Cotizacion3, meta : { title: 'Cotización 2/3'}},
    {path: '/cotizacion/exito', component: CotizacionExito, meta : { title: 'Cotización ✔'}},
    {path: '/atencion', component: Atencion, meta : { title: 'Atención'}},
    {path: '/beneficios', component: Beneficios, meta : { title: 'Beneficios'}},
    {path: '/contacto', component: Contacto, meta : { title: 'Contacto'}},
    {path: '/terminos', component: Terminos, meta : { title: 'Términos y condiciones'}},
    {path: "*", component: PageNotFound, meta : { title: 'Página no encontrada'}}
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