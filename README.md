#createEventCalendar

## Info
Sterc
Sander Drenth <sander@sterc.nl> v1.0  

What is CreateEventCalendar?
CreateEventCalendar uses template variables to generate iCalendar files (.ics) specially for events. CreateEventCalendar makes it possible to dynamically create iCalendar files which visitors can download and add to their personal calendar.

This snippet can under more add an URL, an attachment, a location (and add a map for iOS) with optional automatic geocoding of the address.

Usage

Install:
Create the createEventCalendar snippet and insert the included code. Inside your assets folder create a directory called events where the generated iCal files will be stored.

The snippet accepts the following parameters:
&filePath: Path where the calendar file will be saved, defaults to assets/events directory (define path like /example/)
&fileName: defaults to pagetitle
&tpl: name of chunk to use. Defaults to eventCalendar
&summary: to add a summary of the event
&startDate: start date of the event, format like 2015-05-14 15:53:00 (default output of date TV)
&endDate: end date of the event, format like 2015-05-14 15:53:00 (default output of date TV)
&address: comma delimited list of the addres as street,housenumber,zipcode,city,country
&link: add link to the event
&attachment: add an attachment to the event
&coordinates: latitude,longitude
&geocode: 0 defaults to 1

See below for an example of the snippet code:
[[!createEventCalendar?
    &filePath=`/events/`
    &fileName=`[[*id]]`
    &summary=`[[*introtext]]`
    &startDate=`[[*eventStartDate]]`
    &endDate=`[[*eventEndDate]]`
    &address=`[[*eventLocation]]`
    &link=`[[~[[*id]]]]`
    &attachment=`[[*eventAttachment]]`
    &coordinates=`53.175602,6.182714`
    &geocode=`1`
]]

The script by default uses the pagetitle as the name for the created iCal file. The file download URL is set to the placeholder calendarLink.
Usage like:
<a href="[[+calendarLink]]">Download event</a>

# Free Extra
This is a free extra and the code is publicly available for you to change. The extra is being actively maintained and you're free to put in pull requests which match our roadmap. Please create an issue if the pull request differs from the roadmap so we can make sure we're on the same page.

Need help? [Approach our support desk for paid premium support.](mailto:service@sterc.com)
