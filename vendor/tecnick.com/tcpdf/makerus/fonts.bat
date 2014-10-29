@echo off


set FONTDIR=.\rus_fonts
set FONTS=..\fonts
set UTILSDIR=%FONTS%\utils
set UTIL=%UTILSDIR%\ttf2ufm -a -F
set PHPSCRIPT=%PHPFILE% -q %UTILSDIR%\makefont.php

%PHPFILE% -q php\filestolower.php %FONTDIR%

del /Q *.z
del /Q *.php
del /Q *.ufm

For /R %FONTDIR% %%i In (*.ttf) Do (
call :rename %%i
)

del /Q %FONTDIR%\*.afm
del /Q %FONTDIR%\*.t1a
move /Y %FONTDIR%\*.ufm .\


del /Q %FONTS%\*.z
del /Q %FONTS%\*.php
del /Q %FONTS%\*.ufm

copy /Y %FONTS%\default\*.* %FONTS%

move /Y "*.z" %FONTS%
move /Y "*.php" %FONTS%
move /Y "*.ufm" %FONTS%

%PHPFILE% -q php\no_include_font.php %FONTS%

pause

goto eof

:rename 
set FILE_TTF=%*
set FILE_UFM=%FILE_TTF:ttf=ufm%

%UTIL% %FILE_TTF%
%PHPSCRIPT% %FILE_TTF% %FILE_UFM% true cp1251

:eof