<!DOCTYPE html>
    <div class="background-container">
        <div class="container_individual">

            <span style="font-size:30px;"><span style="color:green;">Climate</span>Master</span>
            <h5 style="text-align: right;">Klimaschutz jetzt</h5>
            <p class="headline">E-Mail Verifizierung bei <span style="color:green">Climate</span>Master</p>
            <p>
            Du hast einen neuen Verifizierungslink angefordert, um deine E-Mail zu bestätigen. 
            Folge dem unten stehenden Button, um dein Klimaschutz Konto zu aktivieren.
            </p>
            <p style="text-align: center;margin:auto;margin-top:20px;margin-bottom:20px;">
                <a style="text-align:center;margin:auto;border-color:green;border-radius:10px;border-style:solid;padding:10px;" href="https://www.climate-master.com/account/verification?userID={{$user->id}}&verificationCode={{$verificationCode}}">Klimaschutzkonto Aktivieren</a>
            </p>

            <p style="text-align: center;">Vielen Dank für die Aktivierung deines ClimateMaster Kontos.</p>
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