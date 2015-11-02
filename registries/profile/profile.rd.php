<?php
/*
 * Created on Nov 17, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
	class _RegistryDescriptor extends SetupFormDescriptor
	{
		function getObj()
		{
			return app()->user();
		}
	}
