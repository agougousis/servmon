# Server Monitoring - ServMon

## Frameworks/Packages/Libraries 

PHP

Laravel 5 PHP Framework
etrepat/baum  (Nested Set pattern for Eloquent ORM)
laravelcollective/html	(HTML and Form Builders for the Laravel Framework)
backup-manager/backup-manager (Framework-agnostic backup manager)
backup-manager/laravel  (Laravel driver for Backup Manager)
paragonie/constant_time_encoding  (Constant-Time character encoding)

Javascript

jQuery 1.11.2	  
CodeSeven/toastr  (non-blocking notifications library)
jsTree (jQuery plugin for tree structures)	https://www.jstree.com/
Bootstrap	http://getbootstrap.com/
Bootstrap Toggle (Bootstrap plugin for toggle buttons) 	http://www.bootstraptoggle.com/






An input validation library for J2EE projects¨

This library provides a ```FormValidator``` class that helps developers validate user input in server-side (servlet) using bean validation, extending at the same time the constraints available by Hibernate Validator. The assignment of data sent to a servlet through a GET or POST request takes place automatically.

For example, the developer that wants to validate a registration form defines e.g a RegistrationForm class annotated with validation constraints, like the following:

```java
@SameAs(testFieldName="repeatPassword",dependOnFieldName="password")
public class RegistrationForm extends BaseForm {
    
    // Properties (with validation annotation)

    @NotBlank
    @Size(min=2,max=30)
    @Alpha
    private String firstname;   
    
    @NotBlank
    @Size(min=2,max=30)
    @Alpha
    private String lastname;
    
    @NotBlank
    @Size(min=6)
    @AlphaNum
    private String username;
    
    @NotBlank
    @Size(min=8,max=30)
    private String password;
    
    @NotBlank
    private String repeatPassword;    
        
    @IsDate(dateFormat="yyyy-MM-dd")
    private String birthdate;

    // Getters
    public String   getFirstname()      {   return firstname;   }
    public String   getLastname()       {   return lastname;    }
    public String   getUsername()       {   return username;    }
    public String   getPassword()       {   return password;    }
    public String   getRepeatPassword() {   return repeatPassword;    }
    public String   getBirthdate()      {   return birthdate;    }
    
    // Setters
    public void setFirstname(String firstname)  {  this.firstname = firstname;  }    
    public void setLastname(String lastname)    {  this.lastname = lastname;    }  
    public void setUsername(String username)    {  this.username = username;    } 
    public void setPassword(String password)    {  this.password = password;    } 
    public void setRepeatPassword(String repeatPassword)    {  this.repeatPassword = repeatPassword;    } 
    public void setBirthdate(String birthdate)    {  this.birthdate = birthdate;    } 

}
```
After that, the validation that takes place in the servlet looks really simple:

```java
  FormValidator validator = new FormValidator("my.package.forms.TestForm");  
  validator.load(request.getParameterMap());
  if(validator.fails()){

	} else {
		
	}    
```

The ```load()``` method loads the posted (in case of a POST request) data from the HttpServletRequest object.
The method ```fails()``` runs the validation process returning a boolean about the success of the process. 

The validator object has two more helpful methods. 

The ```getErrors()``` method that returns a Map<String,String> object, where the key represents the name of the form field that caused the  validation error and the value represents the message about what went wrong. 
The ```getBeanForm()``` returns the bean that holds the values of the form:

```java
	RegistrationForm form = (RegistrationForm) validator.getBeanForm();

	out.write("firstname = "+form.getFirstname()+"<br>");
	out.write("lastname = "+form.getLastname()+"<br>");
```

Getting all the validation errors, along with the field names and values (for field where the validation failed), can be done like this:

```java
f(validator.fails()){
            RegistrationForm form = (RegistrationForm) validator.getBeanForm();
            for (Map.Entry<String, String> entry : validator.getErrors().entrySet()) {
                String fieldName = entry.getKey();
                String errorMessage = entry.getValue();
                String fieldValue = form.getFieldAsString(fieldName);
                out.write(fieldName + " <strong>(problem)</strong> " + errorMessage + " <strong>(old value)</strong> "+fieldValue+"<br>");
            }
        }
```

## Supported validation constraints

Additionally to the constraints provided by Bean Validation API and Hibernate Validator, the following constraints have been implemented:
```
@Alpha		Field,Parameter		String that contains only letters 
@AlphaNum	Field,Parameter		String that contains only letters and digits.
@AlphaPlus	Field,Parameter		String that contains only letters, digits, spaces, dashes and underscores.
@IsDate		Field,Parameter		String representing a date in certain format.
@InArray	Field,Parameter		String chosen from a list of values.
@SameAs		Class,Interface		A property that has to have the same value as another property.
@RequiredWith	Class,Interface		A property is required only if a value has been given to another property.
@RequiredIf	Class,Interface 	A property is required only if another property has a certain value.
```

## Dependencies
```
The libraries that are required and based on which this library has been developed are:
	- hibernate-validator-4.3.0.Final.jar
	- validation-api-1.0.0.GA.jar
	- jboss-logging-3.1.0
	- commons-beansutils-1.9.2.jar
	- commons-collections-3.2.1.jar
```



