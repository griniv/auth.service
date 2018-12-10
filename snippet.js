(function(){
    $.ajax('http://localhost:8080/token',{
        type: 'POST',
        data: {
            login: AMOCRM.widgets.system.amouser,
            api_key: AMOCRM.widgets.system.amohash
        },
        success: function(data){
            console.log(data.token);
        },
        error: function(){
            console.log('Request failed');
        }
    })
})()