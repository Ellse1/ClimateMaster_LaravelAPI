<!DOCTYPE html>
    <div class="background-container">
        <div class="container_individual">

            <span style="font-size:30px;"><span style="color:green;">Climate</span>Master</span>
            <h5 style="text-align: right;">Klimaschutz jetzt</h5>
            <p class="headline">Nachricht bei <span style="color:green">Climate</span>Master</p>

            <p>Du hast eine Nachricht an ClimateMaster geschickt. Hier der Inhalt deiner Nachricht:</p>
            <p>##### Deine Nachricht #####</p>
            <p>{{$email_message->message}}</p>
            <p>##### Ende deiner Nachricht #####</p>
            <br>
            <p>Vielen Dank für dein Nachricht. Wir werden so schnell wie möglich Antworten.</p>
            <br>
            <br>
            <p>Liebe Grüße</p>
            <p>Dein ClimateMaster Team</p>
        </div>
    </div>
<style>
html{
    background-color: whitesmoke;
}
.background-container{
    background-color: whitesmoke;
    height:100%;
}
.container_individual{
    max-width:600px;
    margin:auto;
    margin-top:30px;
    background-color: white;
    padding-top:10px;
}
.headline{
    width:100%;
    font-size:50px;
    text-align: center;
    background-color: #30CDF5;
    padding-top:10px;
    padding-bottom: 10px;
}
</style>