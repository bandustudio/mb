var candidato = {};

const Splash = {
  template: '#splash',
  mounted: function() {
    helper.getFlash()
    $('[data-typer-targets]').typer()
    $.typer.options.textColor = 'black'
  },  
  data: function() {
    return{
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Dash = {
  template: '#dash',
  mounted: function() {
    helper.getFlash()
  },   
  data: function() {
    return{
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cotizacion = {
  template: '#cotizacion',
  mounted: function() {
    var getCandidate = this.getCandidate()

    if(this.cotizacion && this.cotizacion.complete){
      helper.clear('cotizacion')
      this.cotizacion = helper.champ().cotizacion
    }

    helper.collect('cotizacion')

    if(this.cotizacion && this.cotizacion.brand_id){
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
    getVersiones: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/versiones', {marca:this.cotizacion.brand_id}, {emulateJSON:true}).then(function(res){
        this.versiones = res.data.data
        $('select[name="version_id"]').parent().removeClass('is-loading')
        $('select[name="version_id"]').focus()
      }, function(error){
        console.log(error.statusText)
      })
    },
    versionChange : function(e){
      $('select[name="mt_year"]').focus()
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
      versiones : null,
      candidato: candidato,
      cotizacion: helper.champ().cotizacion||{},
      helper : helper,
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cotizacion2 = {
  template: '#cotizacion2',
  mounted: function() {
    helper.collect('cotizacion');
    helper.initAutocomplete('address','cotizacion');
    helper.send('cotizacion')
    $('input[type="hidden"]').trigger("change")
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
            text:'Nuestro asesor se pondrá en contacto con vos para ayudarte a ahorrar.<br>Podés seguir la evolución de tu consulta accediendo a nuestra <a href="' + helper.getAttributes($('html')).clientes + '">portal de clientes y empresas</a> con los datos que te enviamos al mismo email que nos proporcionaste. <br><br>Si todavía no lo hiciste lee nuestros <a href="' + helper.getAttributes($('html')).app + '/terminos">términos y condiciones</a>. <br><br><em>Atte. el equipo de MercedesBenz.com'
          });

          helper.setValue('cotizacion','complete',1)
          helper.send('cotizacion','{"redirect":"/cotizacion/exito"}')
        },1000)   
      }
    }
  },  
  data: function() {
    return{
      cotizacion : helper.champ().cotizacion||{},
      candidato: candidato,
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const CotizacionExito = {
  template: '#cotizacionexito',
  data: function() {
    return{
      cotizacion : helper.champ().cotizacion||{},
      candidato: candidato,
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cuenta = {
  template: '#cuenta',
  mounted: function() {
    helper.collect('cuenta')
  },
  methods : {
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.createImage(files[0])
      this.uploadImage(files[0])
    },
    createImage(file,code) {
      var reader = new FileReader();
      var code = this.code
      reader.onload = (e) => {
        var $id = $('#img'+code);
        $id.css({
          'background-image': 'url(' + e.target.result + ')',
          'background-size': 'cover'
        });
        var exif = EXIF.readFromBinaryFile(new base64ToArrayBuffer(e.target.result));
        helper.fixExifOrientation(exif.Orientation, $id);
      };
      reader.readAsDataURL(file);
    },
    removeImage: function (e) {
      this.image = '';
    }, 
    clickImage : function(code){
      this.upload = true
      this.code = code
      $("#uploads").click()
      return false
    },
    uploadImage : function(file){
      this.upload = true
      var code = this.code
      var formData = new FormData();
      formData.append('uploads[]', file);
      var token = helper.champ('token')
      var that = this
      //loading
      //$('.profile'+type+'--link').text("Subiendo...");
      $.ajax({
        type:'post',
        url: helper.getAttributes($('html')).endpoint + '/upload/avatar/' + code,
        data:formData,
        beforeSend: function (xhr) { 
          xhr.setRequestHeader('Authorization', 'Bearer ' + token.token); 
        },          
        xhr: function() {
          var myXhr = $.ajaxSettings.xhr();
          if(myXhr.upload){
            myXhr.upload.addEventListener('progress',function(e){
              if(e.lengthComputable){
                var max = e.total;
                var current = e.loaded;
                var percentage = (current * 100)/max;

                console.log("subiendo : " + parseInt(percentage))

                if(percentage >= 100) {
                  that.upload = false
                  helper.showTick()
                }
              }
            }, false);
          }
          return myXhr;
        },
        cache:false,
        contentType: false,
        processData: false,
        success:function(res){
          if(res.error){
            console.log(res.error)
          }
          var token = helper.champ('token')
          token.picture = res.url
          localStorage.setItem("token", JSON.stringify(token))
          helper.showTick()
        },
        error: function(data){
          console.log("Hubo un error al subir el archivo");
        }
      })
    }
  },
  data: function() {
    return{
      token : helper.champ('token')||{},
      cuenta: helper.champ().cuenta||{},
      code : "",
      upload : false,      
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cuenta2 = {
  template: '#cuenta2',
  mounted: function() {
    helper.collect('cuenta')
    helper.send('cuenta')
  },
  methods : {
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.createImage(files[0])
      this.uploadImage(files[0])
    },
    createImage(file,code) {
      var reader = new FileReader();
      var code = this.code
      reader.onload = (e) => {
        var $id = $('#img'+code);
        $id.css({
          'background-image': 'url(' + e.target.result + ')',
          'background-size': 'cover'
        });
        var exif = EXIF.readFromBinaryFile(new base64ToArrayBuffer(e.target.result));
        helper.fixExifOrientation(exif.Orientation, $id);
      };
      reader.readAsDataURL(file);
    },
    removeImage: function (e) {
      this.image = '';
    }, 
    clickImage : function(code){
      this.upload = true
      this.code = code
      $("#uploads").click()
      return false
    },
    uploadImage : function(file){
      this.upload = true
      var code = this.code
      var formData = new FormData();
      formData.append('uploads[]', file);
      var token = helper.champ('token')
      var that = this
      //loading
      //$('.profile'+type+'--link').text("Subiendo...");
      $.ajax({
        type:'post',
        url: helper.getAttributes($('html')).endpoint + '/upload/avatar/' + code,
        data:formData,
        beforeSend: function (xhr) { 
          xhr.setRequestHeader('Authorization', 'Bearer ' + token.token); 
        },          
        xhr: function() {
          var myXhr = $.ajaxSettings.xhr();
          if(myXhr.upload){
            myXhr.upload.addEventListener('progress',function(e){
              if(e.lengthComputable){
                var max = e.total;
                var current = e.loaded;
                var percentage = (current * 100)/max;

                console.log("subiendo : " + parseInt(percentage))

                if(percentage >= 100) {
                  that.upload = false
                  helper.showTick()
                }
              }
            }, false);
          }
          return myXhr;
        },
        cache:false,
        contentType: false,
        processData: false,
        success:function(res){
          if(res.error){
            console.log(res.error)
          }
          var token = helper.champ('token')
          token.picture = res.url
          localStorage.setItem("token", JSON.stringify(token))
          helper.showTick()
        },
        error: function(data){
          console.log("Hubo un error al subir el archivo");
        }
      })
    }
  },  
  data: function() {
    return{
      token : helper.champ('token')||{},
      cuenta: helper.champ().cuenta||{},
      code : "",
      upload : false,      
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cuenta3 = {
  template: '#cuenta3',
  mounted: function() {
    helper.initAutocomplete('address','cuenta');
    helper.collect('cuenta')
    helper.send('cuenta')
  },
  methods : {
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.createImage(files[0])
      this.uploadImage(files[0])
    },
    createImage(file,code) {
      var reader = new FileReader();
      var code = this.code
      reader.onload = (e) => {
        var $id = $('#img'+code);
        $id.css({
          'background-image': 'url(' + e.target.result + ')',
          'background-size': 'cover'
        });
        var exif = EXIF.readFromBinaryFile(new base64ToArrayBuffer(e.target.result));
        helper.fixExifOrientation(exif.Orientation, $id);
      };
      reader.readAsDataURL(file);
    },
    removeImage(e) {
      this.image = '';
    }, 
    clickImage(code){
      this.upload = true
      this.code = code
      $("#uploads").click()
      return false
    },
    uploadImage(file){
      this.upload = true
      var code = this.code
      var formData = new FormData();
      formData.append('uploads[]', file);
      var token = helper.champ('token')
      var that = this
      //loading
      //$('.profile'+type+'--link').text("Subiendo...");
      $.ajax({
        type:'post',
        url: helper.getAttributes($('html')).endpoint + '/upload/avatar/' + code,
        data:formData,
        beforeSend: function (xhr) { 
          xhr.setRequestHeader('Authorization', 'Bearer ' + token.token); 
        },          
        xhr: function() {
          var myXhr = $.ajaxSettings.xhr();
          if(myXhr.upload){
            myXhr.upload.addEventListener('progress',function(e){
              if(e.lengthComputable){
                var max = e.total;
                var current = e.loaded;
                var percentage = (current * 100)/max;

                console.log("subiendo : " + parseInt(percentage))

                if(percentage >= 100) {
                  that.upload = false
                  helper.showTick()
                }
              }
            }, false);
          }
          return myXhr;
        },
        cache:false,
        contentType: false,
        processData: false,
        success:function(res){
          if(res.error){
            console.log(res.error)
          }
          var token = helper.champ('token')
          token.picture = res.url
          localStorage.setItem("token", JSON.stringify(token))
          helper.showTick()
        },
        error: function(data){
          console.log("Hubo un error al subir el archivo");
        }
      })
    },
    actualizarCuenta : function(){
      var button = $('.cotizacion .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')  
        setTimeout(function(){      
          helper.setFlash({
            title:"Tu Cuenta está al día!",
            text:'Los datos de tu cuenta han sido actualizados. Gracias por mantener al día tus datos.'
          });
          helper.verifyStatus()
          helper.send('cuenta','{"redirect":"/cuenta/exito"}');
        },1000)   
      }
    }
  },  
  data: function() {
    return{
      token : helper.champ('token')||{},
      cuenta: helper.champ().cuenta||{},
      code : "",
      upload : false,      
      settings: helper.getAttributes($('html'))
    }
  }
}

const CuentaExito = {
  template: '#cuentaexito',
  data: function() {
    return{
      token : helper.champ('token')||{},
      cuenta: helper.champ().cuenta||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Billing = {
  template: '#billing',
  mounted: function() {
    helper.collect('billing')
  },
  data: function() {
    return{
      token : helper.champ('token')||{},
      billing: helper.champ().billing||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Billing2 = {
  template: '#billing2',
  mounted: function() {
    helper.collect('billing')
    helper.send('billing')
  },
  data: function() {
    return{
      token : helper.champ('token')||{},
      billing: helper.champ().billing||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const BillingExito = {
  template: '#billingexito',
  data: function() {
    return{
      token : helper.champ('token')||{},
      billing: helper.champ().billing||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Polizas = {
  template: '#polizas',
  methods : {
    getLeads: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/polizas').then(function(response){
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.polizas = response.data.data
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    actualizarPoliza : function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/cotizacion/solicitar/' + id).then(function(response){
        this.polizas = response.data.data
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    reclamarPoliza : function(id){
      var that = this
      swal({   
        title: "Reclamar Póliza",   
        text: "¿Estás seguro que deseas iniciar un reclamo con esta póliza?",
        type: "warning",
        showCancelButton: true,   
        closeOnConfirm: false,   
        showLoaderOnConfirm: true,
      }, function(){   
        that.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/archivar/'+id).then(function(response){
          that.getLeads()
          swal.close()
        }, function(response){
          swal.close()
          helper.verifyFailResponse(response)
        })
      })      
    },
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.createImage(files[0])
      this.uploadImage(files[0])
    },
    createImage(file,code) {
      var reader = new FileReader();
      var code = this.code
      reader.onload = (e) => {
        var $id = $('#img'+code);
        $id.css({
          'background-image': 'url(' + e.target.result + ')',
          'background-size': 'cover'
        });
        var exif = EXIF.readFromBinaryFile(new base64ToArrayBuffer(e.target.result));
        helper.fixExifOrientation(exif.Orientation, $id);
      };
      reader.readAsDataURL(file);
    },
    removeImage: function (e) {
      this.image = '';
    }, 
    clickImage : function(code){
      this.upload = true
      this.code = code
      $("#uploads").click()
      return false
    },
    uploadImage : function(file){
      this.upload = true
      var code = this.code
      var formData = new FormData();
      formData.append('uploads[]', file);
      var token = helper.champ('token')
      var that = this
      //loading
      //$('.profile'+type+'--link').text("Subiendo...");
      $.ajax({
        type:'post',
        url: helper.getAttributes($('html')).endpoint + '/upload/avatar/' + code,
        data:formData,
        beforeSend: function (xhr) { 
          xhr.setRequestHeader('Authorization', 'Bearer ' + token.token); 
        },          
        xhr: function() {
          var myXhr = $.ajaxSettings.xhr();
          if(myXhr.upload){
            myXhr.upload.addEventListener('progress',function(e){
              if(e.lengthComputable){
                var max = e.total;
                var current = e.loaded;
                var percentage = (current * 100)/max;

                console.log("subiendo : " + parseInt(percentage))

                if(percentage >= 100) {
                  that.upload = false
                  helper.showTick()
                }
              }
            }, false);
          }
          return myXhr;
        },
        cache:false,
        contentType: false,
        processData: false,
        success:function(res){
          if(res.error){
            console.log(res.error)
          }
          var token = helper.champ('token')
          token.picture = res.url
          localStorage.setItem("token", JSON.stringify(token))
          helper.showTick()
        },
        error: function(data){
          console.log("Hubo un error al subir el archivo");
        }
      })
    },
    clickCotizacion : function(item){
      var that = this

      setTimeout(function(){
        if(item.status == 'vigente' && item.doc_poliza && that.es_poliza){
          return that.descargarPoliza(item.doc_poliza)
        }

        if(!that.upload){
          if(item.plans.length){
            router.push('/polizas/' + item.code)
          }
        }

        this.es_poliza = false
      },50)
      return false
    },
    getUpload : function(data,di,i){
      if(i===undefined) i = 0
      if(di===undefined) di = '/img/placeholder-image.jpg'
      var file_url = helper.getAttributes($('html')).assets + di
      if(data && data[i]){
        return data[i].file_url
      }
      return file_url
    }
  },  
  mounted: function() {
    helper.collect('polizas')
    this.getLeads() 
  },
  data: function() {
    return{
      polizas: null,
      es_poliza : false,
      cotizacion : helper.champ().cotizacion||{},
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cotizaciones = {
  template: '#cotizaciones',
  methods : {
    getLeads: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/cotizaciones').then(function(response){
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.cotizaciones = response.data.data
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.createImage(files[0])
      this.uploadImage(files[0])
    },
    createImage(file,code) {
      //var image = new Image();
      var reader = new FileReader();
      var code = this.code
      //var vm = this;

      reader.onload = (e) => {
        //vm.image = e.target.result;
        var $id = $('#img'+code);
        $id.css({
          'background-image': 'url(' + e.target.result + ')',
          'background-size': 'cover'
        });
        var exif = EXIF.readFromBinaryFile(new base64ToArrayBuffer(e.target.result));
        helper.fixExifOrientation(exif.Orientation, $id);
      };
      reader.readAsDataURL(file);
    },
    removeImage: function (e) {
      this.image = '';
    }, 
    clickCard : function(){
      this.upload = false
      return false
    },
    clickImage : function(code){
      this.upload = true
      this.code = code
      $("#uploads").click()
      return false
    },
    uploadImage : function(file){

      this.upload = true
      var code = this.code
      var formData = new FormData();
      formData.append('uploads[]', file);
      var token = helper.champ('token')
      var that = this
      //loading
      //$('.profile'+type+'--link').text("Subiendo...");

      $.ajax({
          type:'post',
          url: helper.getAttributes($('html')).endpoint + '/upload/lead/' + code,
          data:formData,
          beforeSend: function (xhr) { 
            xhr.setRequestHeader('Authorization', 'Bearer ' + token.token); 
          },          
          xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            if(myXhr.upload){
              myXhr.upload.addEventListener('progress',function(e){
                if(e.lengthComputable){
                  var max = e.total;
                  var current = e.loaded;
                  var percentage = (current * 100)/max;

                  console.log("subiendo : " + parseInt(percentage))

                  if(percentage >= 100) {
                    that.upload = false
                    helper.showTick()
                  }
                }
              }, false);
            }
            return myXhr;
          },
          cache:false,
          contentType: false,
          processData: false,
          success:function(response){
            if(response.error){
              console.log(response.error)
            }
            helper.showTick();
          },
          error: function(data){
              console.log("Hubo un error al subir el archivo");
          }
      });
    },
    clickCotizacion : function(item){
      var that = this
      setTimeout(function(){
        if(!that.upload){
          if(item.plans.length){
            router.push('/cotizaciones/' + item.code)
          }
        }
      },50)
      return false
    },
    getUpload : function(data,di,i){
      if(i===undefined) i = 0
      if(di===undefined) di = '/img/placeholder-image.jpg'
      var file_url = helper.getAttributes($('html')).assets + di
      if(data && data[i]){
        return data[i].file_url
      }
      return file_url
    },
    descartarCotizacion : function(id){
      var that = this
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/archivar/'+id).then(function(response){
        /*setTimeout(function(){
          that.getLeads()            
        },1000)*/
      }, function(response){
        helper.verifyFailResponse(response)
      })      
    }
  },  
  mounted: function() {
    helper.collect('cotizaciones')
    helper.getFlash()
    this.getLeads() 
  },
  data: function() {
    return{
      cotizaciones: null,
      cotizacion : helper.champ().cotizacion||{},
      code : "",
      upload : false,
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cobertura = {
  template: '#cobertura',
  methods : {
    descargarPoliza : function(doc){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/poliza/descargar/' + code).then(function(response){
        this.polizas = response.data.data
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    getLead: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes' + location.pathname).then(function(response){
        this.lead = response.data.data
        if(!response.data.data){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    solicitarCobertura : function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/polizas/solicitar/' + id).then(function(response){
        helper.verifyResponse(response)
        helper.setFlash({
          title:"Felicitaciones, tu solicitud fue recibida",
          text:"Te notificaremos cuando la operación se complete."
        })
        helper.showTick()
        router.push('/cotizaciones')
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    cancelarCobertura : function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/polizas/cancelar/' + id).then(function(response){
        helper.verifyResponse(response)
        helper.setFlash({
          title:"La solicitud de cancelación fue recibida",
          text:"Te notificaremos cuando la operación se complete."
        })
        router.push('/cotizaciones')
      }, function(response){
        helper.verifyFailResponse(response)
      })
    }
  },  
  mounted: function() {
    var getLead = this.getLead() 
  },
  data: function() {
    return{
      lead: [],
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Reclamos = {
  template: '#reclamos',
  methods : {
    getLeads: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/reclamos').then(function(response){
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.reclamos = response.data.data
      }, function(response){
        helper.verifyFailResponse(response)
      })
    }
  },  
  mounted: function() {
    helper.collect('reclamos')
    var getLeads = this.getLeads() 
  },
  data: function() {
    return{
      reclamos: null,
      cotizacion : helper.champ().cotizacion||{},
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Notificaciones = {
  template: '#notificaciones',
  methods : {
    getItems: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/notificaciones').then(function(response){
        if(!response.data.unread.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.unread = response.data.unread.data
        this.read = response.data.read.data
      }, function(response){
        helper.verifyFailResponse(response)
      })
    },
    seeMore : function(){
      this.more = true
    }
  },  
  mounted: function() {
    helper.collect('notificaciones')
    var getItems = this.getItems() 
  },
  data: function() {
    return{
      more:false,
      read: [],
      unread: [],
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Ingresar = {
  template: '#ingresar',
  mounted: function() {
    helper.collect('ingresar')
    helper.getFlash()
  },
  methods : {
    accederMercedesBenz : function(){
      var button = $('.ingresar .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')     
        helper.send('ingresar', '{}', function(res){
          if(res.status === 'success'){
            var title =  res.data.first_name ? 'Te damos la bienvenida de nuevo, ' + res.data.first_name + '!' : 'Bienvenido' 
            localStorage.setItem("token", JSON.stringify(res.data))
            swal({
              type:'success',
              title:title,
              text:'Te estamos redireccionando a tu página de perfil...',
              html:true
            })
            setTimeout(function(){
              swal.close()
              router.push('/dash')  
            },3000)
          } else {
            button.removeClass('is-loading')
            swal({
              type:'error',
              title:'Email o clave incorrecta',
              text:'Por favor revisá tus datos.<br>¿Ya probaste desactivando las mayúsculas?',
              html:true
            })
          }
        })
      }
    }
  },  
  data: function() {
    return{
      settings: helper.getAttributes($('html'))
    }
  }
}

const Registro = {
  template: '#registro',
  mounted: function() {
    helper.collect('registro')
    //helper.emailOrPhone('emailorphone')
  },
  methods : {
    registrarUsuario : function(){
      if(!$('input[name="accept_terms"]').is(':checked')){
        return swal({   
          title: "Atención",
          text: '<h4>Es necesario que leas y aceptes nuestros términos y condiciones para continuar.</h4><p><a href="' + helper.getAttributes($('html')).app + '/terminos" target="_blank">Leer Términos y condiciones</a></p><div class="inline-background accept-terms-arrow"></div>',
          html:true,
          type: "warning"
        })
      }
      var button = $('.registro .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')
        setTimeout(function(){
          helper.setFlash({
            title:"Felicitaciones, tu cuenta ha sido creada",
            text:"Te enviamos un enlace para que valides tu cuenta a " + helper.champ().registro.emailorphone + "<br>Revisa tu email para continuar."
          })
          helper.send('registro', '{"redirect":"/"}')
        },1000)
      }
    }
  },  
  data: function() {
    return{
      cotizacion : helper.champ().cotizacion||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const RecuperarClave = {
  template: '#recuperarclave',
  mounted: function() {
    helper.collect('recuperarclave')
    //helper.emailOrPhone('emailorphone')
  },
  methods : {
    recuperarClave : function(){
      var button = $('.recuperarclave .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')
        setTimeout(function(){
          helper.setFlash({
            title:"Generaste una solicitud de actualización de contraseña.",
            text:"Te enviamos un enlace para que actualices tu contraseña a " + helper.champ().recuperarclave.emailorphone
          })
          helper.send('recuperarclave', '{"redirect":"/"}')
        },1000)
      }
    }
  },  
  data: function() {
    return{
      cotizacion : helper.champ().cotizacion||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const ActualizarClave = {
  template: '#actualizarclave',
  mounted: function() {
    helper.collect('actualizarclave')
  },
  methods : {
    actualizarClave : function(){
      var button = $('.actualizarclave .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')
        setTimeout(function(){
          helper.setFlash({
            title:"Tu clave ha sido actualizada",
            text:"Ya podés iniciar sesión con tu nuevo acceso."
          })
          helper.send('actualizarclave', '{"redirect":"/"}')
        },1000)   
      }
    }
  },  
  data: function() {
    return{
      settings: helper.getAttributes($('html'))
    }
  }
}

const Guia = {
  template: '#guia',
  mounted: function() {
    var hash = this.$route.hash
    var vm = this
    setTimeout(function() { 
      vm.scrollFix(vm.$route.hash)
    }, 10)
  },
  methods : {
    scrollFix : function(refName) {
      var element = this.$refs[refName]
      if(element){
        var top = element.offsetTop
        window.scrollTo(0, top)
      }
    }
  },  
  data: function() {
    return{
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const DatosUtiles = {
  template: '#datosutiles',
  mounted: function() {
    setTimeout(function() { this.scrollFix(this.$route.hash)}, 10)
  },
  methods : {
    scrollFix : function(refName) {
      var element = this.$refs[refName]
      if(element){
        var top = element.offsetTop
        window.scrollTo(0, top)
      }
    }
  },  
  data: function() {
    return{
      token : helper.champ('token')||{},
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
    {path: '/', component: Splash, meta : { name: 'Clientes'}},
    {path: '/opener', component: Opener, meta : { title: 'Redirigiendo...'}},    
    {path: '/guia', component: Guia, meta : { name: 'Guía'}},
    {path: '/ingresar', component: Ingresar, meta : { name: 'Iniciar sesión', icon: 'sign-in-alt'}},
    {path: '/registro', component: Registro, meta : { name: 'Crear cuenta', icon: 'sign-in-alt'}},
    {path: '/recuperar-acceso', component: RecuperarClave, meta : { name: 'Olvidé mi contraseña'}},
    {path: '/actualizar-clave', component: ActualizarClave, meta : { name: 'Actualizar Contraseña'}},
    {path: '/dash', component: Dash, meta : { name: 'Dashboard', icon: 'bars', requiresAuth: true}},    
    {path: '/cotizacion', component: Cotizacion, meta : { name: 'Cotización 1/2', icon:'file', requiresAuth: true}},
    {path: '/cotizacion/2', component: Cotizacion2, meta : { name: 'Cotización 2/2', icon:'file', requiresAuth: true}},
    {path: '/cotizacion/exito', component: CotizacionExito, meta : { name: 'Cotización OK', icon: 'check', requiresAuth: true}},
    {path: '/cuenta', component: Cuenta, meta : { name: 'Cuenta 1/3', icon:'user-circle', requiresAuth: true}},    
    {path: '/cuenta/2', component: Cuenta2, meta : { name: 'Cuenta 2/3', icon:'user-circle', requiresAuth: true}},    
    {path: '/cuenta/3', component: Cuenta3, meta : { name: 'Cuenta 3/3', icon:'user-circle', requiresAuth: true}},    
    {path: '/cuenta/exito', component: CuentaExito, meta : { name: 'Cuenta ✔', icon:'check', requiresAuth: true}},    
    {path: '/billing', component: Billing, meta : { name: 'Facturación 1/2', icon:'credit-card', requiresAuth: true}},    
    {path: '/billing/2', component: Billing2, meta : { name: 'Facturación 2/2', icon:'credit-card', requiresAuth: true}},    
    {path: '/billing/exito', component: BillingExito, meta : { name: 'Facturación ✔', icon:'check', requiresAuth: true}},    
    {path: '/polizas', component: Polizas, meta : { name: 'Pólizas', icon:'file-pdf', requiresAuth: true}},    
    {path: '/polizas/:code', component: Cobertura, meta : { name: 'Cobertura', icon:'file', requiresAuth: true}},    
    {path: '/cotizaciones', component: Cotizaciones, meta : { name: 'Cotizaciones', icon:'file', requiresAuth: true}},    
    {path: '/cotizaciones/:code', component: Cobertura, meta : { name: 'Cobertura', icon:'file', requiresAuth: true}},    
    {path: '/reclamos', component: Reclamos, meta : { name: 'Reclamos', icon:'user-circle', requiresAuth: true}},    
    {path: '/datosutiles', component: DatosUtiles, meta : { name: 'Datos Útiles', icon:'phone', requiresAuth: true}},    
    {path: '/notificaciones', component: Notificaciones, meta : { name: 'Notificaciones', icon:'bell', requiresAuth: true}},    
    {path: "*", component: PageNotFound, meta : { name: 'Página no encontrada'}}
  ]
})

router.beforeEach(function (to, from, next) { 
  var token = helper.champ('token').token
  document.title = to.meta.name
  helper.verifyStatus()
  setTimeout(ping,1000)
  if(to.meta.requiresAuth) {
    if(token) {
      next()
    } else {
      next('/')
    }    
  } else {
    next()
  }
})

router.afterEach(function (to, from, next) {
  setTimeout(function() {
    if(to.path!='/'){
      helper.breadcrumb(to.meta)
    }
    $('.navbar-menu, navbar-burger').removeClass('is-active')
    window.scrollTo(0, 0)
  }, 1)
})

Vue.http.interceptors.push(function(request, next) {
  var token = helper.champ('token')
  //request.headers.set('Access-Control-Allow-Credentials', true)
  request.headers.set('Authorization', 'Bearer '+ token.token)
  request.headers.set('Content-Type', 'application/json')
  request.headers.set('Accept', 'application/json')
  next()
})

const app = new Vue({ 
  router : router,
  created: function () {
    $('.loading, .hidden-loading').removeClass('loading hidden-loading')
  },
  methods : {
    token: function(){
      return helper.champ('token')
    },
    cerrarMercedesBenz : function(){
      return helper.cerrarMercedesBenz()
    },
  }
}).$mount('#app')

$(function(){
  setInterval(ping,30000)
  ping()
})

$.ajaxSetup({
  beforeSend : function(xhr) {
    xhr.setRequestHeader('Authorization', 'Bearer ' + helper.champ('token').token || "" )
  }
})
