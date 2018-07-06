$.ajaxSetup({
  beforeSend : function(xhr) {
    xhr.setRequestHeader('Authorization', 'Bearer ' + helper.champ('token').token || "" )
  }
})

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
  },
}

const Dash = {
  template: '#dash',
  methods : {
    getDash: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/dash').then(response => {
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.dash = response.data.data
         this.draw()
      }, response => {
        helper.verifyFailResponse(response)
      })
    },
    draw : function(){
      var ctx = document.getElementById("myChart");
      var labels = []
      var count = []
      var data = []

      this.dash.forEach(function(a,b){
        if(a.status){
          if($.inArray( a.status, labels) == -1){
            labels.push(a.status)
          }

          if(! count[a.status]){
            count[a.status] = 0
          }
          count[a.status]++;
        }
      })

      for(var i in count){
        data.push(count[i])
      }

      var myChart = new Chart(ctx, {
          type: 'bar',
          data: {
              labels: labels,
              datasets: [{
                  label: '# estado',
                  data: data,
                  backgroundColor: [
                      'rgba(255, 99, 132, 0.2)',
                      'rgba(54, 162, 235, 0.2)',
                      'rgba(255, 206, 86, 0.2)',
                      'rgba(75, 192, 192, 0.2)',
                      'rgba(153, 102, 255, 0.2)',
                      'rgba(255, 159, 64, 0.2)'
                  ],
                  borderColor: [
                      'rgba(255,99,132,1)',
                      'rgba(54, 162, 235, 1)',
                      'rgba(255, 206, 86, 1)',
                      'rgba(75, 192, 192, 1)',
                      'rgba(153, 102, 255, 1)',
                      'rgba(255, 159, 64, 1)'
                  ],
                  borderWidth: 1
              }]
          },
          options: {
              scales: {
                  yAxes: [{
                      ticks: {
                          beginAtZero:true
                      }
                  }]
              }
          }
      })
    }
  },
  mounted: function() {
    this.getDash() 
   
  },  
  data: function() {
    return{
      dash : null,
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
  methods : {
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

const Cotizaciones = {
  template: '#cotizaciones',
  methods : {
    getUpload : function(data,di,i){
      if(i===undefined) i = 0
      if(di===undefined) di = '/img/placeholder-image.jpg'
      var file_url = helper.getAttributes($('html')).assets + di
      if(data && data[i]){
        return data[i].file_url
      }
      return file_url
    },
    getLeads: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/cotizaciones').then(response => {
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.cotizaciones = response.data.data
      }, response => {
        helper.verifyFailResponse(response)
      })
    },
    reasignarCotizacion : function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/leads/gestores', {}, {emulateJSON:true}).then(response => {
        var html = "Por favor elige un gestor de la lista para reasignar. Esta operación no podrá ser revertida!<br>";
        var that = this
        swal({
          title: 'Reasignar esta cotización a:',
          input: 'select',
          inputOptions: response.data,
          showCancelButton: true,
          inputValidator: function (value) {
            return new Promise(function (resolve, reject) {
              if (value !== '') {
                resolve();
              } else {
                reject('You need to select a Tier');
              }
            })
          }
        }).then(function (uid) {
          that.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/leads/gestor', {id:id,uid:uid}, {emulateJSON:true}).then(response => {
            swal({
              type: 'success',
              html: 'La cotización fue reasignada'
            })
            that.getLeads() 
          })
        })
      })
    },
    enviarCotizacion : function(id){
      var $f = $('#part'+id)
      var that = this
      swal({   
        title: "Enviar cotización",   
        html: "¿Estás seguro que deseas enviar esta cotización?",
        type: "warning",
        showCancelButton: true
      }).then((result) => {  
        if(result){
          that.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/cotizacion/enviar/' + id, $f.serializeJSON(), {emulateJSON:true}).then(response => {
            swal.close()
            $f.parents('.box').fadeOut('slow',function(){
              $(this).remove()
            })
            //that.getLeads() 
          }, response => {
            swal.close()
            helper.verifyFailResponse(response)
          })
        }
      })      
    },
    descartarCotizacion : function(id){
      var that = this
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/archivar/'+id).then(function(response){
        that.getLeads()
        swal.close()
      }, function(response){
        swal.close()
        helper.verifyFailResponse(response)
      })
    }    
  },  
  mounted: function() {
    helper.collect('cotizaciones')
    this.getLeads() 
  },
  data: function() {
    return{
      cotizaciones: null,
      cotizacion : helper.champ().cotizacion||{},
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cotizacion = {
  template: '#cotizacion',
  mounted: function() {

    helper.clear('cotizacion')
    helper.collect('cotizacion')

    var getVersiones = this.getVersiones

    $('select[name="brand_id"]').on('change',function(){
      $('select[name="version_id"]').parent().addClass('is-loading')
      getVersiones($(this).val())
    })

    $('input[type="hidden"]').trigger("change")
  },  
  methods: {
    getVersiones: function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/app/versiones', {marca:id}, {emulateJSON:true}).then(function(res){
        this.versiones = res.data.data
        $('select[name="version_id"]').parent().removeClass('is-loading')
      }, function(error){
        console.log(error.statusText)
      })
    }
  },     
  data: function() {
    return{
      versiones : null,
      cotizacion: helper.champ().cotizacion||{},
      helper : helper,
      token : helper.champ('token')||{},
      settings: helper.getAttributes($('html'))
    }
  }
}

const Polizas = {
  template: '#polizas',
  methods : {
    getUpload : function(data,di,i){
      if(i===undefined) i = 0
      if(di===undefined) di = '/img/placeholder-image.jpg'
      var file_url = helper.getAttributes($('html')).assets + di
      if(data && data[i]){
        return data[i].file_url
      }
      return file_url
    },    
    getLeads: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/polizas').then(response => {
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.polizas = response.data.data
      }, response => {
        helper.verifyFailResponse(response)
      })
    },
    actualizarEstado : function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/leads/estados', {}, {emulateJSON:true}).then(response => {
        var html = "Por favor elige un gestor de la lista para reasignar. Esta operación no podrá ser revertida!<br>";
        var that = this
        swal({
          title: 'Reasignar esta cotización a:',
          input: 'select',
          inputOptions: response.data,
          showCancelButton: true,
          inputValidator: function (value) {
            return new Promise(function (resolve, reject) {
              if (value !== '') {
                resolve();
              } else {
                reject('You need to select a Tier');
              }
            })
          }
        }).then(function (status) {
          that.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/leads/estado', {id:id,status:status}, {emulateJSON:true}).then(response => {
            swal({
              type: 'success',
              html: 'La póliza fue actualizada'
            })
            that.getLeads() 
          })
        })
      })
    },
    reasignarCotizacion : function(id){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/leads/gestores', {}, {emulateJSON:true}).then(response => {
        var html = "Por favor elige un gestor de la lista para reasignar. Esta operación no podrá ser revertida!<br>";
        var that = this
        swal({
          title: 'Reasignar esta cotización a:',
          input: 'select',
          inputOptions: response.data,
          showCancelButton: true,
          inputValidator: function (value) {
            return new Promise(function (resolve, reject) {
              if (value !== '') {
                resolve();
              } else {
                reject('You need to select a Tier');
              }
            })
          }
        }).then(function (uid) {
          that.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/leads/gestor', {id:id,uid:uid}, {emulateJSON:true}).then(response => {
            swal({
              type: 'success',
              html: 'La cotización fue reasignada'
            })
            that.getLeads() 
          })
        })
      })
    },    
    descartarCotizacion : function(id){
      var that = this
      swal({   
        title: "Descartar cotización",   
        text: "¿Estás seguro que deseas descartar esta cotización?",
        type: "warning",
        showCancelButton: true,   
        showCancelButton: true
      }).then((result) => {  
        if(result){
          that.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/archivar/'+id).then(response => {
            that.getLeads()
            swal.close()
          }, response => {
            swal.close()
            helper.verifyFailResponse(response)
          })
        }
      })      
    },
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      this.uploadDoc(files[0])
    },
    removeImage: function (e) {
      this.doc = '';
    }, 
    clickImage : function(code){
      this.code = code
      $("#uploads"+code).trigger('click')
      return false
    },
    uploadDoc : function(file){
      this.upload = true
      var code = this.code
      var formData = new FormData();
      formData.append('uploads[]', file);
      var token = helper.champ('token')
      var that = this
      $.ajax({
        type:'post',
        url: helper.getAttributes($('html')).endpoint + '/upload/poliza/' + code,
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
          swal({
            type:'success',
            title:'Póliza actualizada',
            html:'La póliza ha sido actualizada'
          }).then(function (uid) {
            helper.showTick()
          })
        },
        error: function(data){
          console.log("Hubo un error al subir el archivo");
        }
      })
    }    
  },  
  mounted: function() {
    helper.collect('polizas')
    this.getLeads() 
  },
  data: function() {
    return{
      polizas: null,
      cotizacion : helper.champ().cotizacion||{},
      token : helper.champ('token')||{},
      filters : helper.filters,
      settings: helper.getAttributes($('html'))
    }
  }
}

const Cobertura = {
  template: '#cobertura',
  methods : {
    getCoberturas: function(){
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/reclamos').then(response => {
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.reclamos = response.data.data
      }, response => {
        helper.verifyFailResponse(response)
      })
    }
  },  
  mounted: function() {
    var getCoberturas = this.getCoberturas() 
  },
  data: function() {
    return{
      coberturas: null,
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
      this.$http.post(helper.getAttributes($('html')).endpoint + '/clientes/reclamos').then(response => {
        if(!response.data.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.reclamos = response.data.data
      }, response => {
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
      this.$http.post(helper.getAttributes($('html')).endpoint + '/gestion/notificaciones').then(response => {
        if(!response.data.unread.data.length){
          $('.noresponse.hide').addClass('fadeIn').removeClass('hide')
        }
        this.unread = response.data.unread.data
        this.read = response.data.read.data
      }, response => {
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
            localStorage.setItem("token", JSON.stringify(res.data))
            swal({
              type:'success',
              title:'Te damos la bienvenida de nuevo, ' + res.data.first_name + '!',
              html:'Te estamos redireccionando a tu página de perfil...'
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
              html:'Por favor revisá tus datos.<br>¿Ya probaste desactivando las mayúsculas?'
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
      var button = $('.registro .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')
        setTimeout(function(){
          helper.setFlash({title:"Felicitaciones, tu cuenta ha sido creada",text:"Te enviamos un enlace para que valides tu cuenta a " + helper.champ().registro.emailorphone})
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
          helper.setFlash({title:"Generaste una solicitud de actualización de contraseña.",text:"Te enviamos un enlace para que actualices tu contraseña a " + helper.champ().recuperarclave.emailorphone})
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
    //helper.emailOrPhone('emailorphone')
  },
  methods : {
    recuperarClave : function(){
      var button = $('.actualizarclave .button.rounded-button-grey')
      if(!button.hasClass('disabled')){
        button.addClass('is-loading')
        setTimeout(function(){
          helper.setFlash({title:"Felicitaciones, tu clave ha sido reseteada",text:"Te enviamos un enlace para que valides tu cuenta a " + helper.champ().registro.emailorphone})
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

const Guia = {
  template: '#guia',
  mounted: function() {
    var vm = this
    setTimeout(() => vm.scrollFix(vm.$route.hash), 10)
  },
  methods : {
    scrollFix(refName) {
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
    var vm = this
    setTimeout(() => vm.scrollFix(vm.$route.hash), 10)
  },
  methods : {
    scrollFix(refName) {
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
    {path: '/', component: Splash, meta : { name: 'Gestión'}},
    {path: '/guia', component: Guia, meta : { name: 'Guía'}},
    {path: '/ingresar', component: Ingresar, meta : { name: 'Ingresar'}},
    {path: '/registro', component: Registro, meta : { name: 'Registro'}},
    {path: '/recuperar-acceso', component: RecuperarClave, meta : { name: 'Olvidé mi contraseña'}},
    {path: '/actualizar-clave', component: ActualizarClave, meta : { name: 'Actualizar Contraseña'}},
    {path: '/dash', component: Dash, meta : { name: 'Dashboard', icon:'bars', requiresAuth: true}},    
    {path: '/cotizaciones', component: Cotizaciones, meta : { name: 'Cotizaciones', icon:'file', requiresAuth: true}},
    {path: '/polizas', component: Polizas, meta : { name: 'Pólizas', icon:'file-pdf', requiresAuth: true}},    
    {path: '/cuenta', component: Cuenta, meta : { name: 'Mi Cuenta 1/3', icon:'user-circle', requiresAuth: true}},    
    {path: '/cuenta/2', component: Cuenta2, meta : { name: 'Mi Cuenta 2/3', icon:'user-circle', requiresAuth: true}},    
    {path: '/cuenta/3', component: Cuenta3, meta : { name: 'Mi Cuenta 3/3', icon:'user-circle', requiresAuth: true}},    
    {path: '/cuenta/exito', component: CuentaExito, meta : { name: 'Mi Cuenta', icon:'check', requiresAuth: true}},    
    {path: '/cobertura/:id', component: Cobertura, meta : { name: 'Cobertura', icon:'file', requiresAuth: true}},    
    {path: '/reclamos', component: Reclamos, meta : { name: 'Reclamos', icon:'warning', requiresAuth: true}},    
    {path: '/datosutiles', component: DatosUtiles, meta : { name: 'Datos útiles', icon:'phone', requiresAuth: true}},    
    {path: '/notificaciones', component: Notificaciones, meta : { name: 'Notificaciones', icon:'bell', requiresAuth: true}},    
    {path: "*", component: PageNotFound, meta : { name: 'Página no encontrada'}}
  ]
})

router.beforeEach(function (to, from, next) { 
  var token = helper.champ('token').token
  document.title = to.meta.name
  if(to.meta.requiresAuth) {
    helper.verifyStatus()
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
  setTimeout(() => {
    if(to.path!='/'){
      helper.breadcrumb(to.meta)
    }
    $('.navbar-menu, navbar-burger').removeClass('is-active')
    window.scrollTo(0, 0)
  }, 1)
})

Vue.http.interceptors.push((request, next) => {
  var token = helper.champ('token')
  //request.headers.set('Access-Control-Allow-Credentials', true)
  request.headers.set('Authorization', 'Bearer '+ token.token)
  request.headers.set('Content-Type', 'application/json')
  request.headers.set('Accept', 'application/json')
  next()
})

const app = new Vue({ 
  router,
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
  setInterval(ping,10000)
  ping()
})