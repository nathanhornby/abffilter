#ABF Members Filter

##Notice

This extension was funded by and subsequently open-sourced by [Crocodile Talk](https://www.crocodiletalk.com/). This extension is provided as-is and may contain issues, use at own risk.

##Synopsis

ABFFilter Extension for Symphony CMS connects IP banning of the Anti Brute Force extension with the Members extension.

##Installation and configuration

Move the abffilter directory to your extension folder and install the extension as usual (Symstem->Extensions->install/enable).

If you want to use the unban via email functionality, you need to create a deticated page that contains the `ABFFilter: unban IP` event that ships with this extesnion. The pages' url handle must also be configured System->Preferences->ABF Filter->Unban URL. Be sure that `ubhash` is set as first URL parameter on that page.

You will also need to add two deligates to the members extension's `extension.driver.php` file, here is the section you need to replace, just copy this over the existing section: https://gist.github.com/nathanhornby/773065b51faf0e092ab8

## Usage

### BF detection and member login

Brute force detection will work on Memberlogin events and Member Password reset event, so there is no need to configure this seperately. 

It is possible to add the `ABFFilter: Ban status` datasource to pages where you need to display information about the visitor's IP status. The `ABFFilter: Ban status` datasource will add two parameter:

 - `$is-currently-banned` (will be `yes` unless a user is not blocked or blacklisted)
 - `$is-blacklisted` (will be as `yes` if users' IP is blacklisted. Note that in this case, `$is-currently-banned` will be set to `no`)

### Sending unban links via email gateway

The event `ABFFilter: Send unban email` can be utilized to send an unban link via email. Please refer to the example snippet from the events' description for information on the field markup. 

exaple markup:

        <form method="post" action="" enctype="multipart/form-data">
            <input name="MAX_FILE_SIZE" type="hidden" value="5242880" />
            <label>Your IP is currently banned. Try to unban your IP with your email address.
                <input name="email" type="text"/>
            </label>
            <input name="abffilter-action[sendmail]" type="submit" value="Submit" />
        </form>

 
example markup when used together with the `ABFFilter: Ban status` datasource

	<form method="post" action="" enctype="multipart/form-data">
		<input name="MAX_FILE_SIZE" type="hidden" value="5242880" />
		<xsl:choose>
			<xsl:when test="$is-currently-banned = 'yes' and $is-blacklisted = 'no'">
				<label>Your IP is currently banned. Try to unbann you IP with your email address.
					<input name="email" type="text"/>
				</label>
				<input name="abffilter-action[sendmail]" type="submit" value="Submit" />
			</xsl:when>
			<xsl:when test="$is-currently-banned = 'no' and $is-blacklisted = 'yes'">
				<label>Your IP Blacklisted due to too many banns. please contact service.
				</label>
			</xsl:when>
			<xsl:otherwise>
				<label>Username
					<input name="fields[username]" type="text" />
				</label>
				<fieldset>
					<label>Password
						<input name="fields[password]" type="password" />
					</label>
				</fieldset>
				<input name="member-action[login]" type="submit" value="Submit" />

			</xsl:otherwise>
		</xsl:choose>
     </form>
 
 
### Unlocking IP bans with a hash

The event `ABFFilter: unban IP` can be utilized to unban blocked IP adresses. Attach this filter to the page that is configured in `System->Preferences->ABF Filter->Unban`. Please do not forget to set `ubhash` as first page parameter.    
 
