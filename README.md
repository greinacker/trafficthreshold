## Traffic Threshold Pepper

This is a "pepper" extension for [Mint](http://haveamint.com), an application for 
web stats.  The pepper sends an alert via email when your site hits a certain number of
page views per minute.

Note that access to certain APIs is required for this to work; when you install
the pepper it will test to ensure it will be able to work, and show you a
message if not.

More information at [Traffic threshold pepper extension for Mint stats](http://www.rassoc.com/gregr/weblog/2011/04/26/traffic-threshold-pepper-extension-for-mint-stats/)

## Documentation

It’s designed to be super fast. No extra script is used on the client side. The 
preferences are stored in the Mint database using the Pepper API, so Mint will load 
them (and it’s designed to load pepper preferences efficiently). The actual page 
view counting, though, isn’t done with the database or a file, but rather uses a 
unix system V shared memory segment. Web requests are served from multiple 
processes, and thus the page view counter needs to be saved somewhere where they 
can all access it; shared memory (with synchronization to protect against 
simultaneous updates) is one of the fastest ways to do this.

The shared memory is allocated on installation (when the pepper is testing to see 
if the required APIs are available), and will be cleaned up when the pepper is 
uninstalled. It won’t work on every system – for example, if you’re on a shared 
hosting plan, the required APIs may be disabled. But you can give it a shot, and 
you’ll see a message during configuration if the pepper can’t be installed.

It measures using clock minutes, rather than a 60-second sliding window. There are 
technical reasons for this, but most folks will never notice. And it will only work 
for sites hosted on a single server.

The "gregreinacker" directory is there because of a Mint requirement that each
pepper is installed in a directory with the author's name.

Please feel free to contribute, and send a pull request.

## DEPENDENCIES

* [Mint](http://haveamint.com)
* Assumes there is a default mailer installed on your system

## USAGE

End-users can download the current released extension from the [Mint site](http://haveamint.com/peppermill/pepper/102/traffic_threshold/).

## Credits

Original author: [Greg Reinacker](http://www.rassoc.com/gregr/weblog)

## LICENSE:

Copyright (C) 2011 Greg Reinacker

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
