$(document).ready(function (){ // wird ausgeführt sobald die Seite geladen ist
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    today = yyyy + '-' + mm + '-' + dd;
    $('#newExpirationDate').attr('min',today);
    $("#details").hide();
    loadAppointments();
});

function loadAppointments(){
    $("#listitems").empty();
    $("#details").hide();
    $.ajax({
        type:"GET",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "getAppointmentList", param: ""},
        dataType: "json",
        success: function(response){
            console.log(response);
            $.each(response, function (i, p){
                $("#listitems").append("<li class='list-group-item'><b>" + p['title'] + "</b></li>");
                $("#listitems li:last-child").attr("data-id", p['app_id']);
            });
            $("#listitems").on("click", "li", loadDetails);
        },
        error: function(e){
            $("#error").empty();
            $("#error").hide();
            $("#error").append("<li class='list-group-item' id='error'>Something went wrong :(</li>");
            $("#error").show();
        }
    });
    $("#listitems").show();
}

function loadDetails(){
    $("#detailitem").empty();
    $("#details").show();
    console.log($(this).attr('data-id'));
    // AJAX mit Suche nach id
    $.ajax({
        type:"GET",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "getAppointmentById", param: $(this).attr('data-id')},
        dataType: "json",
        success: function(response){
            console.log(response);
            console.log(response.creator);
            $("#detailitem").append("<li class='list-group-item'><b>" + response.title + "</b></li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.creator + "</li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.description + "</li>");
            $("#detailitem").append("<li class='list-group-item'>" + response.location + "</li>");
        },
        error: function(response){
        }
    });
}

function sendNewData(){
    var newTitle = $("#newTitle").val();
    var newCreator = $("#newCreator").val();
    var newDescription = $("#newDescription").val();
    var newLocation = $("#newLocation").val();
    var newExpirationDate = $("#newExpirationDate").val();
    var newData = {title: newTitle, creator: newCreator, description: newDescription, location: newLocation, expirationDate: newExpirationDate}
    newData = JSON.stringify(newData);
    $("input").val("");
    console.log(newData);
    /*$.ajax({
        type: "POST",
        url: "../backend/serviceHandler.php",
        chache: false,
        data: {method: "newAppointment", param: newData}
    }); */
    loadAppointments();
}

function addVotes(){
    // AJAX call zu addVotes
    var partName = $("#partName").val();
    var check1 = null;
    var check2 = null;
    if($("#checkbox1").is(":checked")){
        check1 = $("#checkbox1").val();
    }
    if($("#checkbox2").is(":checked")){
        check2 = $("#checkbox2").val();
    }
    var newVotes = {name: partName, slots: check1, slots: check2}
    $("input").val("");
    console.log(newVotes);
    loadAppointments();
}