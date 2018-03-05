# Banner plugin

### Description:
- Drag&drop to upload and sort.
- The order of banners and sliders will be applied after saving. 

### Note:
- Should remove slider of the index default page:
   - a. Go to the admin/content/page  (コンテンツ管理 ページ管理)
   - b. Select ページ編集 at the TOPページ page.
   - c. Change content of the Top page to (remove javascript and main block ):
   ```
   {#
   This file is part of EC-CUBE
   
   Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
   
   http://www.lockon.co.jp/
   
   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
   #}
   {% extends 'default_frame.twig' %}
   {% set body_class = 'front_page' %}
   ```
   d. Save and see the result.