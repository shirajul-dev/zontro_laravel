      <div class="dark" style="display: contents;">
          <aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
              class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 duration-300 ease-linear dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
              <!-- SIDEBAR HEADER -->
              <div class="flex items-center justify-between pt-8 pb-7 w-full px-2 overflow-hidden"
                  :class="sidebarToggle ? 'justify-center' : 'justify-between'">

                  <!-- Expanded State -->
                  <div class="logo !flex items-center justify-between w-full" :class="sidebarToggle ? 'hidden' : 'flex'">
                      <a href="{{ route('merchant.dashboard') }}">
                          <img class="h-7 object-contain" src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" />
                      </a>
                      <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                          {{ config('zontropay.version') }}
                      </span>
                  </div>

                  <!-- Collapsed State -->
                  <a href="{{ route('merchant.dashboard') }}" class="logo-icon" :class="sidebarToggle ? 'block' : 'hidden'">
                      <img class="h-8 w-8 object-contain mx-auto" src="{{ asset('assets/images/favicon-dark.png') }}" alt="Logo" />
                  </a>
              </div>
              <!-- SIDEBAR HEADER -->

              <div class="mt-6 flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
                  <!-- Sidebar Menu -->
                  <nav x-data="{
                      selected: '{{ request()->is('merchant/dashboard*') ? 'Dashboard' : (request()->is('merchant/settings*') ? 'Settings' : (request()->is('merchant/system*') ? 'System' : '')) }}'
                  }">
                      <!-- Menu Group -->
                      <div>
                          <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                              <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                                  MENU
                              </span>

                              <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                                  class="mx-auto fill-current menu-group-icon" width="24" height="24"
                                  viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                      fill="" />
                              </svg>
                          </h3>

                          <ul class="flex flex-col gap-4 mb-6">
                              <!-- Menu Item Dashboard -->
                              <li>
                                  <a href="{{ route('merchant.dashboard') }}" @click="selected = 'Dashboard'"
                                      class="menu-item group"
                                      :class="selected === 'Dashboard' ? 'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="selected === 'Dashboard' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Dashboard
                                      </span>

                                  </a>
                              </li>
                              <!-- Menu Item Dashboard -->

                              <!-- Menu Item Calendar -->
                              <!-- Menu Item Settings -->
                              <li>
                                  <a href="{{ route('merchant.settings') }}" @click="selected = 'Settings'"
                                      class="menu-item group"
                                      :class="selected === 'Settings' ? 'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="selected === 'Settings' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M10.4858 3.5L13.5182 3.5C13.9233 3.5 14.2518 3.82851 14.2518 4.23377C14.2518 5.9529 16.1129 7.02795 17.602 6.1682C17.9528 5.96567 18.4014 6.08586 18.6039 6.43667L20.1203 9.0631C20.3229 9.41407 20.2027 9.86286 19.8517 10.0655C18.3625 10.9253 18.3625 13.0747 19.8517 13.9345C20.2026 14.1372 20.3229 14.5859 20.1203 14.9369L18.6039 17.5634C18.4013 17.9142 17.9528 18.0344 17.602 17.8318C16.1129 16.9721 14.2518 18.0471 14.2518 19.7663C14.2518 20.1715 13.9233 20.5 13.5182 20.5H10.4858C10.0804 20.5 9.75182 20.1714 9.75182 19.766C9.75182 18.0461 7.88983 16.9717 6.40067 17.8314C6.04945 18.0342 5.60037 17.9139 5.39767 17.5628L3.88167 14.937C3.67903 14.586 3.79928 14.1372 4.15026 13.9346C5.63949 13.0748 5.63946 10.9253 4.15025 10.0655C3.79926 9.86282 3.67901 9.41401 3.88165 9.06303L5.39764 6.43725C5.60034 6.08617 6.04943 5.96581 6.40065 6.16858C7.88982 7.02836 9.75182 5.9539 9.75182 4.23399C9.75182 3.82862 10.0804 3.5 10.4858 3.5ZM13.5182 2L10.4858 2C9.25201 2 8.25182 3.00019 8.25182 4.23399C8.25182 4.79884 7.64013 5.15215 7.15065 4.86955C6.08213 4.25263 4.71559 4.61859 4.0986 5.68725L2.58261 8.31303C1.96575 9.38146 2.33183 10.7477 3.40025 11.3645C3.88948 11.647 3.88947 12.3531 3.40026 12.6355C2.33184 13.2524 1.96578 14.6186 2.58263 15.687L4.09863 18.3128C4.71562 19.3814 6.08215 19.7474 7.15067 19.1305C7.64015 18.8479 8.25182 19.2012 8.25182 19.766C8.25182 20.9998 9.25201 22 10.4858 22H13.5182C14.7519 22 15.7518 20.9998 15.7518 19.7663C15.7518 19.2015 16.3632 18.8487 16.852 19.1309C17.9202 19.7476 19.2862 19.3816 19.9029 18.3134L21.4193 15.6869C22.0361 14.6185 21.6701 13.2523 20.6017 12.6355C20.1125 12.3531 20.1125 11.647 20.6017 11.3645C21.6701 10.7477 22.0362 9.38152 21.4193 8.3131L19.903 5.68667C19.2862 4.61842 17.9202 4.25241 16.852 4.86917C16.3632 5.15138 15.7518 4.79856 15.7518 4.23377C15.7518 3.00024 14.7519 2 13.5182 2ZM9.6659 11.9999C9.6659 10.7103 10.7113 9.66493 12.0009 9.66493C13.2905 9.66493 14.3359 10.7103 14.3359 11.9999C14.3359 13.2895 13.2905 14.3349 12.0009 14.3349C10.7113 14.3349 9.6659 13.2895 9.6659 11.9999ZM12.0009 8.16493C9.88289 8.16493 8.1659 9.88191 8.1659 11.9999C8.1659 14.1179 9.88289 15.8349 12.0009 15.8349C14.1189 15.8349 15.8359 14.1179 15.8359 11.9999C15.8359 9.88191 14.1189 8.16493 12.0009 8.16493Z"
                                              fill=""></path>
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Brand Settings
                                      </span>
                                  </a>
                              </li>
                              <!-- Menu Item Settings End -->
                              
                              <!-- Menu Item Manage System -->
                              <li>
                                  <a href="{{ route('merchant.system') }}" @click="selected = 'System'"
                                      class="menu-item group"
                                      :class="selected === 'System' ? 'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="selected === 'System' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15.93c-3.13-.26-5.67-2.8-5.93-5.93H9v-2H7.07c.26-3.13 2.8-5.67 5.93-5.93V7h2v-.93c3.13.26 5.67 2.8 5.93 5.93H19v2h-1.93c-.26 3.13-2.8 5.67-5.93 5.93V17h-2v.93z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Manage System
                                      </span>
                                  </a>
                              </li>

                              <li>
                                  <a href="calendar.html"
                                      @click="selected = (selected === 'Calendar' ? '':'Calendar')"
                                      class="menu-item group"
                                      :class="(selected === 'Calendar') && (page === 'calendar') ? 'menu-item-active' :
                                      'menu-item-inactive'">
                                      <svg :class="(selected === 'Calendar') && (page === 'calendar') ? 'menu-item-icon-active' :
                                      'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Calendar
                                      </span>
                                  </a>
                              </li>
                              <!-- Menu Item Calendar -->

                              <!-- Menu Item Profile -->
                              <li>
                                  <a href="profile.html" @click="selected = (selected === 'Profile' ? '':'Profile')"
                                      class="menu-item group"
                                      :class="(selected === 'Profile') && (page === 'profile') ? 'menu-item-active' :
                                      'menu-item-inactive'">
                                      <svg :class="(selected === 'Profile') && (page === 'profile') ? 'menu-item-icon-active' :
                                      'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          User Profile
                                      </span>
                                  </a>
                              </li>
                              <!-- Menu Item Profile -->

                              <!-- Menu Item Forms -->
                              <li>
                                  <a href="#" @click.prevent="selected = (selected === 'Forms' ? '':'Forms')"
                                      class="menu-item group"
                                      :class="(selected === 'Forms') || (page === 'formElements' ||
                                          page === 'formLayout' ||
                                          page === 'proFormElements' || page === 'proFormLayout') ?
                                      'menu-item-active' :
                                      'menu-item-inactive'">
                                      <svg :class="(selected === 'Forms') || (page === 'formElements' ||
                                          page === 'formLayout' ||
                                          page === 'proFormElements' || page === 'proFormLayout') ?
                                      'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Forms
                                      </span>

                                      <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                          :class="[(selected === 'Forms') ? 'menu-item-arrow-active' :
                                              'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                          ]"
                                          width="20" height="20" viewBox="0 0 20 20" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                      </svg>
                                  </a>

                                  <!-- Dropdown Menu Start -->
                                  <div class="overflow-hidden transform translate"
                                      :class="(selected === 'Forms') ? 'block' : 'hidden'">
                                      <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                          class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                          <li>
                                              <a href="form-elements.html" class="menu-dropdown-item group"
                                                  :class="page === 'formElements' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Form Elements
                                              </a>
                                          </li>
                                      </ul>
                                  </div>
                                  <!-- Dropdown Menu End -->
                              </li>
                              <!-- Menu Item Forms -->

                              <!-- Menu Item Tables -->
                              <li>
                                  <a href="#" @click.prevent="selected = (selected === 'Tables' ? '':'Tables')"
                                      class="menu-item group"
                                      :class="(selected === 'Tables') || (page === 'basicTables' ||
                                      page === 'dataTables') ?
                                      'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="(selected === 'Tables') || (page === 'basicTables' ||
                                      page === 'dataTables') ?
                                      'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M3.25 5.5C3.25 4.25736 4.25736 3.25 5.5 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V18.5C20.75 19.7426 19.7426 20.75 18.5 20.75H5.5C4.25736 20.75 3.25 19.7426 3.25 18.5V5.5ZM5.5 4.75C5.08579 4.75 4.75 5.08579 4.75 5.5V8.58325L19.25 8.58325V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H5.5ZM19.25 10.0833H15.416V13.9165H19.25V10.0833ZM13.916 10.0833L10.083 10.0833V13.9165L13.916 13.9165V10.0833ZM8.58301 10.0833H4.75V13.9165H8.58301V10.0833ZM4.75 18.5V15.4165H8.58301V19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5ZM10.083 19.25V15.4165L13.916 15.4165V19.25H10.083ZM15.416 19.25V15.4165H19.25V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15.416Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Tables
                                      </span>

                                      <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                          :class="[(selected === 'Tables') ? 'menu-item-arrow-active' :
                                              'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                          ]"
                                          width="20" height="20" viewBox="0 0 20 20" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                      </svg>
                                  </a>

                                  <!-- Dropdown Menu Start -->
                                  <div class="overflow-hidden transform translate"
                                      :class="(selected === 'Tables') ? 'block' : 'hidden'">
                                      <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                          class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                          <li>
                                              <a href="basic-tables.html" class="menu-dropdown-item group"
                                                  :class="page === 'basicTables' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Basic Tables
                                              </a>
                                          </li>
                                      </ul>
                                  </div>
                                  <!-- Dropdown Menu End -->
                              </li>
                              <!-- Menu Item Tables -->

                              <!-- Menu Item Pages -->
                              <li>
                                  <a href="#" @click.prevent="selected = (selected === 'Pages' ? '':'Pages')"
                                      class="menu-item group"
                                      :class="(selected === 'Pages') || (page === 'fileManager' ||
                                          page === 'pricingTables' ||
                                          page === 'blank' || page === 'page404' || page === 'page500' ||
                                          page === 'page503' || page === 'success' || page === 'faq' ||
                                          page === 'comingSoon' || page === 'maintenance') ? 'menu-item-active' :
                                      'menu-item-inactive'">
                                      <svg :class="(selected === 'Pages') || (page === 'fileManager' ||
                                          page === 'pricingTables' ||
                                          page === 'blank' || page === 'page404' || page === 'page500' ||
                                          page === 'page503' || page === 'success' || page === 'faq' ||
                                          page === 'comingSoon' || page === 'maintenance') ? 'menu-item-icon-active' :
                                      'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M8.50391 4.25C8.50391 3.83579 8.83969 3.5 9.25391 3.5H15.2777C15.4766 3.5 15.6674 3.57902 15.8081 3.71967L18.2807 6.19234C18.4214 6.333 18.5004 6.52376 18.5004 6.72268V16.75C18.5004 17.1642 18.1646 17.5 17.7504 17.5H16.248V17.4993H14.748V17.5H9.25391C8.83969 17.5 8.50391 17.1642 8.50391 16.75V4.25ZM14.748 19H9.25391C8.01126 19 7.00391 17.9926 7.00391 16.75V6.49854H6.24805C5.83383 6.49854 5.49805 6.83432 5.49805 7.24854V19.75C5.49805 20.1642 5.83383 20.5 6.24805 20.5H13.998C14.4123 20.5 14.748 20.1642 14.748 19.75L14.748 19ZM7.00391 4.99854V4.25C7.00391 3.00736 8.01127 2 9.25391 2H15.2777C15.8745 2 16.4468 2.23705 16.8687 2.659L19.3414 5.13168C19.7634 5.55364 20.0004 6.12594 20.0004 6.72268V16.75C20.0004 17.9926 18.9931 19 17.7504 19H16.248L16.248 19.75C16.248 20.9926 15.2407 22 13.998 22H6.24805C5.00541 22 3.99805 20.9926 3.99805 19.75V7.24854C3.99805 6.00589 5.00541 4.99854 6.24805 4.99854H7.00391Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Pages
                                      </span>

                                      <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                          :class="[(selected === 'Pages') ? 'menu-item-arrow-active' :
                                              'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                          ]"
                                          width="20" height="20" viewBox="0 0 20 20" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                      </svg>
                                  </a>

                                  <!-- Dropdown Menu Start -->
                                  <div class="overflow-hidden transform translate"
                                      :class="(selected === 'Pages') ? 'block' : 'hidden'">
                                      <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                          class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                          <li>
                                              <a href="blank.html" class="menu-dropdown-item group"
                                                  :class="page === 'blank' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Blank Page
                                              </a>
                                          </li>
                                          <li>
                                              <a href="404.html" class="menu-dropdown-item group"
                                                  :class="page === 'page404' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  404 Error
                                              </a>
                                          </li>
                                      </ul>
                                  </div>
                                  <!-- Dropdown Menu End -->
                              </li>
                              <!-- Menu Item Pages -->
                          </ul>
                      </div>

                      <!-- Others Group -->
                      <div>
                          <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                              <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                                  others
                              </span>

                              <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                                  class="mx-auto fill-current menu-group-icon" width="24" height="24"
                                  viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                      fill="" />
                              </svg>
                          </h3>

                          <ul class="flex flex-col gap-4 mb-6">
                              <!-- Menu Item Charts -->
                              <li>
                                  <a href="#" @click.prevent="selected = (selected === 'Charts' ? '':'Charts')"
                                      class="menu-item group"
                                      :class="(selected === 'Charts') || (page === 'lineChart' || page === 'barChart' ||
                                          page === 'pieChart') ? 'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="(selected === 'Charts') || (page === 'lineChart' || page === 'barChart' ||
                                          page === 'pieChart') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M12 2C11.5858 2 11.25 2.33579 11.25 2.75V12C11.25 12.4142 11.5858 12.75 12 12.75H21.25C21.6642 12.75 22 12.4142 22 12C22 6.47715 17.5228 2 12 2ZM12.75 11.25V3.53263C13.2645 3.57761 13.7659 3.66843 14.25 3.80098V3.80099C15.6929 4.19606 16.9827 4.96184 18.0104 5.98959C19.0382 7.01734 19.8039 8.30707 20.199 9.75C20.3316 10.2341 20.4224 10.7355 20.4674 11.25H12.75ZM2 12C2 7.25083 5.31065 3.27489 9.75 2.25415V3.80099C6.14748 4.78734 3.5 8.0845 3.5 12C3.5 16.6944 7.30558 20.5 12 20.5C15.9155 20.5 19.2127 17.8525 20.199 14.25H21.7459C20.7251 18.6894 16.7492 22 12 22C6.47715 22 2 17.5229 2 12Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Charts
                                      </span>

                                      <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                          :class="[(selected === 'Charts') ? 'menu-item-arrow-active' :
                                              'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                          ]"
                                          width="20" height="20" viewBox="0 0 20 20" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                      </svg>
                                  </a>

                                  <!-- Dropdown Menu Start -->
                                  <div class="overflow-hidden transform translate"
                                      :class="(selected === 'Charts') ? 'block' : 'hidden'">
                                      <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                          class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                          <li>
                                              <a href="line-chart.html" class="menu-dropdown-item group"
                                                  :class="page === 'lineChart' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Line Chart
                                              </a>
                                          </li>
                                          <li>
                                              <a href="bar-chart.html" class="menu-dropdown-item group"
                                                  :class="page === 'barChart' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Bar Chart
                                              </a>
                                          </li>
                                      </ul>
                                  </div>
                                  <!-- Dropdown Menu End -->
                              </li>
                              <!-- Menu Item Charts -->

                              <!-- Menu Item Ui Elements -->
                              <li>
                                  <a href="#"
                                      @click.prevent="selected = (selected === 'UIElements' ? '':'UIElements')"
                                      class="menu-item group"
                                      :class="(selected === 'UIElements') || (page === 'alerts' || page === 'avatars' ||
                                          page === 'badge' || page === 'buttons' || page === 'buttonsGroup' ||
                                          page === 'cards' || page === 'carousel' || page === 'dropdowns' ||
                                          page === 'images' || page === 'list' || page === 'modals' ||
                                          page === 'videos') ? 'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="(selected === 'UIElements') || (page === 'alerts' || page === 'avatars' ||
                                          page === 'badge' || page === 'breadcrumb' || page === 'buttons' ||
                                          page === 'buttonsGroup' || page === 'cards' || page === 'carousel' ||
                                          page === 'dropdowns' || page === 'images' || page === 'list' ||
                                          page === 'modals' || page === 'notifications' || page === 'popovers' ||
                                          page === 'progress' || page === 'spinners' || page === 'tooltips' ||
                                          page === 'videos') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M11.665 3.75618C11.8762 3.65061 12.1247 3.65061 12.3358 3.75618L18.7807 6.97853L12.3358 10.2009C12.1247 10.3064 11.8762 10.3064 11.665 10.2009L5.22014 6.97853L11.665 3.75618ZM4.29297 8.19199V16.0946C4.29297 16.3787 4.45347 16.6384 4.70757 16.7654L11.25 20.0365V11.6512C11.1631 11.6205 11.0777 11.5843 10.9942 11.5425L4.29297 8.19199ZM12.75 20.037L19.2933 16.7654C19.5474 16.6384 19.7079 16.3787 19.7079 16.0946V8.19199L13.0066 11.5425C12.9229 11.5844 12.8372 11.6207 12.75 11.6515V20.037ZM13.0066 2.41453C12.3732 2.09783 11.6277 2.09783 10.9942 2.41453L4.03676 5.89316C3.27449 6.27429 2.79297 7.05339 2.79297 7.90563V16.0946C2.79297 16.9468 3.27448 17.7259 4.03676 18.1071L10.9942 21.5857L11.3296 20.9149L10.9942 21.5857C11.6277 21.9024 12.3732 21.9024 13.0066 21.5857L19.9641 18.1071C20.7264 17.7259 21.2079 16.9468 21.2079 16.0946V7.90563C21.2079 7.05339 20.7264 6.27429 19.9641 5.89316L13.0066 2.41453Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          UI Elements
                                      </span>

                                      <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                          :class="[(selected === 'UIElements') ? 'menu-item-arrow-active' :
                                              'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                          ]"
                                          width="20" height="20" viewBox="0 0 20 20" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                      </svg>
                                  </a>

                                  <!-- Dropdown Menu Start -->
                                  <div class="overflow-hidden transform translate"
                                      :class="(selected === 'UIElements') ? 'block' : 'hidden'">
                                      <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                          class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                          <li>
                                              <a href="alerts.html" class="menu-dropdown-item group"
                                                  :class="page === 'alerts' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Alerts
                                              </a>
                                          </li>
                                          <li>
                                              <a href="avatars.html" class="menu-dropdown-item group"
                                                  :class="page === 'avatars' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Avatars
                                              </a>
                                          </li>
                                          <li>
                                              <a href="badge.html" class="menu-dropdown-item group"
                                                  :class="page === 'badge' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Badges
                                              </a>
                                          </li>
                                          <li>
                                              <a href="buttons.html" class="menu-dropdown-item group"
                                                  :class="page === 'buttons' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Buttons
                                              </a>
                                          </li>
                                          <li>
                                              <a href="images.html" class="menu-dropdown-item group"
                                                  :class="page === 'images' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Images
                                              </a>
                                          </li>
                                          <li>
                                              <a href="videos.html" class="menu-dropdown-item group"
                                                  :class="page === 'videos' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Videos
                                              </a>
                                          </li>
                                      </ul>
                                  </div>
                                  <!-- Dropdown Menu End -->
                              </li>
                              <!-- Menu Item Ui Elements -->

                              <!-- Menu Item Authentication -->
                              <li>
                                  <a href="#"
                                      @click.prevent="selected = (selected === 'Authentication' ? '':'Authentication')"
                                      class="menu-item group"
                                      :class="(selected === 'Authentication') || (page === 'basicChart' ||
                                          page === 'advancedChart') ? 'menu-item-active' : 'menu-item-inactive'">
                                      <svg :class="(selected === 'Authentication') || (page === 'basicChart' ||
                                          page === 'advancedChart') ? 'menu-item-icon-active' :
                                      'menu-item-icon-inactive'"
                                          width="24" height="24" viewBox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M14 2.75C14 2.33579 14.3358 2 14.75 2C15.1642 2 15.5 2.33579 15.5 2.75V5.73291L17.75 5.73291H19C19.4142 5.73291 19.75 6.0687 19.75 6.48291C19.75 6.89712 19.4142 7.23291 19 7.23291H18.5L18.5 12.2329C18.5 15.5691 15.9866 18.3183 12.75 18.6901V21.25C12.75 21.6642 12.4142 22 12 22C11.5858 22 11.25 21.6642 11.25 21.25V18.6901C8.01342 18.3183 5.5 15.5691 5.5 12.2329L5.5 7.23291H5C4.58579 7.23291 4.25 6.89712 4.25 6.48291C4.25 6.0687 4.58579 5.73291 5 5.73291L6.25 5.73291L8.5 5.73291L8.5 2.75C8.5 2.33579 8.83579 2 9.25 2C9.66421 2 10 2.33579 10 2.75L10 5.73291L14 5.73291V2.75ZM7 7.23291L7 12.2329C7 14.9943 9.23858 17.2329 12 17.2329C14.7614 17.2329 17 14.9943 17 12.2329L17 7.23291L7 7.23291Z"
                                              fill="" />
                                      </svg>

                                      <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                          Authentication
                                      </span>

                                      <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                          :class="[(selected === 'Authentication') ? 'menu-item-arrow-active' :
                                              'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : ''
                                          ]"
                                          width="20" height="20" viewBox="0 0 20 20" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                      </svg>
                                  </a>

                                  <!-- Dropdown Menu Start -->
                                  <div class="overflow-hidden transform translate"
                                      :class="(selected === 'Authentication') ? 'block' : 'hidden'">
                                      <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                          class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                          <li>
                                              <a href="signin.html" class="menu-dropdown-item group"
                                                  :class="page === 'signin' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Sign In
                                              </a>
                                          </li>
                                          <li>
                                              <a href="signup.html" class="menu-dropdown-item group"
                                                  :class="page === 'signup' ? 'menu-dropdown-item-active' :
                                                      'menu-dropdown-item-inactive'">
                                                  Sign Up
                                              </a>
                                          </li>
                                      </ul>
                                  </div>
                                  <!-- Dropdown Menu End -->
                              </li>
                              <!-- Menu Item Authentication -->
                          </ul>
                      </div>
                  </nav>
                  <!-- Sidebar Menu -->

                  <!-- Promo Box -->
                  <div :class="sidebarToggle ? 'lg:hidden' : ''"
                      class="mx-auto mb-10 w-full max-w-60 rounded-2xl bg-gray-50 px-4 py-5 text-center dark:bg-white/[0.03]">
                      <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
                          #1 Tailwind CSS Dashboard
                      </h3>
                      <p class="mb-4 text-gray-500 text-theme-sm dark:text-gray-400">
                          Leading Tailwind CSS Admin Template with 400+ UI Component and Pages.
                      </p>
                      <a href="https://tailadmin.com/pricing" target="_blank" rel="nofollow"
                          class="flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600">
                          Purchase Plan
                      </a>
                  </div>
                  <!-- Promo Box -->
              </div>
          </aside>
      </div>
